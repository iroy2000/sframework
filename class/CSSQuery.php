<?php
/**
 *	This package contains one class for using Cascading Style Sheet
 *	selectors to retrieve elements from a DOMDocument object similarly
 *	to DOMXPath does with XPath selectors
 *
 *	@author Sam Shull <sam.shull@jhspecialty.com>
 *	@version 1.0.1
 *
 *	@copyright Copyright (c) 2009 Sam Shull <sam.shull@jhspeicalty.com>
 *	@license <http://www.opensource.org/licenses/mit-license.html>
 *
 *	Permission is hereby granted, free of charge, to any person obtaining a copy
 *	of this software and associated documentation files (the "Software"), to deal
 *	in the Software without restriction, including without limitation the rights
 *	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *	copies of the Software, and to permit persons to whom the Software is
 *	furnished to do so, subject to the following conditions:
 *
 *	The above copyright notice and this permission notice shall be included in
 *	all copies or substantial portions of the Software.
 *
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *	THE SOFTWARE.
 *
 *
 *	CHANGES:
 *		06-08-2009 - added normalize-space function to CSSQuery::className
 *					 and removed unecessary sprintf(s) in favor of " strings
 *					 and fixed runtime pass-by-reference errors
 */

/**
 *	Perform a CSS query on a DOMDocument using DOMXPath
 *
 *	<code>
 *		$doc = new DOMDocument();
 *		$doc->loadHTML('<html><body><p>hello world</p></body></html>');
 *		$css = new CSSQuery($doc);
 *		print count( $css->query("p:contains('hello world')") );
 *	</code>
 *
 */
class CSSQuery {
    /**
     *	This PCRE string matches one valid css selector at a time
     *
     *	@const string
     */
    const CHUNKER = "/^\s*([#\.>~\+:\[,]?)\s*(\*|[^\*#\.>~\+:\[\]\)\(\s,]*)\s*/";

    /**
     *	This PCRE string matches one psuedo css selector at a time
     *
     *	@const string
     */
    const PSUEDO = "/^\s*:([\w\-]+)\s*(\(\s*([^\(\)]*(\([^\(\)]*\))?)?\s*\))?\s*/";

    /**
     *	This PCRE string matches one css attribute selector at a time
     *
     *	@const string
     */
    const ATTRIBUTES = '/\[@?([\w\-]+(\|[\w\-]+)?)\s*((\S*)=\s*([\'"]?)(?(5)([^\\5]*)\\5|([^\]]+)))?\s*\]/i';

    /**
     *	An array of functions representing psuedo selectors
     *
     *	@access public
     *
     *	@staticvar array
     */
    public static $filters;

    /**
     *	An array of functions representing attribute selectors
     *
     *	@access public
     *
     *	@staticvar array
     */
    public static $attributeFilters;

    /**
     *	An instance of DOMXPath for finding the information on the document
     *
     *	@access public
     *
     *	@var DOMXPath
     */
    public $xpath;

    /**
     *	The document that the queries will originate from
     *
     *	@access public
     *
     *	@var DOMDocument
     */
    public $document;

    /**
     *	Initialize the object - opens a new DOMXPath
     *
     *	@access public
     *
     *	@param DOMDocument $document
     */
    public function __construct (DOMDocument &$document) {
        $this->xpath = new DOMXPath($document);
        $this->document =& $document;
    }

    /**
     *	register a namespace
     *
     *	@access public
     *
     *	@param string $prefix
     *	@param string $URI
     *
     *	@returns boolean
     */
    public function registerNamespace ($prefix, $URI) {
        return $this->xpath->registerNamespace($prefix, $URI);
    }

    /**
     *	Get an array of DOMNodes that match a CSS query expression
     *
     *	@access public
     *
     *	@param string $expression
     *	@param mixed $context - a DOMNode or an array of DOMNodes
     *
     *	@returns array
     */
    public function query ($expression, $context=null) {
        $original_context = func_num_args() < 3 ? $context : func_get_arg(2);

        $current = $context instanceof DOMNode ? array($context) : self::makeArray($context);

        $new = array();

        $m = array("");

        if ($expression && preg_match(self::CHUNKER, $expression, $m)) {
            //replace a pipe with a semi-colon in a selector
            //for namespace uses
            $m[2] = $m[2] ? str_replace("|", ":", $m[2]) : "*";

            switch ($m[1]) {
                case ",": {
                        $new = $this->query(ltrim(substr($expression, strpos($expression, $m[1]) + 1)), array(), $original_context);
                        return self::unique(array_merge($current, $new));
                    }
                //#id
                case "#": {
                        $new = $this->id($m[2], $current);
                        break;
                    }
                //.class
                case ".": {
                        $new = $this->className($m[2], $current);
                        break;
                    }
                // > child
                case ">": {
                        $new = $this->children($m[2], $current);
                        break;
                    }
                // + adjacent sibling
                case "+": {
                        $new = $this->adjacentSibling($m[2],$current);
                        break;
                    }
                // ~ general sibling
                case "~": {
                        $new = $this->generalSibling($m[2], $current);
                        break;
                    }
                //:psuedo-filter
                case ":": {
                        if ($m[2] == "root") {
                            $new = array($this->document->documentElement);
                        }
                        //a psuedo selector is a filter
                        elseif (preg_match(self::PSUEDO, $expression, $n)) {
                            if ($n[1] && isset(self::$filters[$n[1]]) && is_callable(self::$filters[$n[1]])) {
                                if (!$current) {
                                    $current = self::makeArray($this->xpath->query("//*"));
                                }

                                $i = 0;

                                foreach ($current as $elem) {
                                    if ($item = call_user_func(self::$filters[$n[1]], $elem, $i++, $n, $current, $this)) {
                                        if ($item instanceof DOMNode) {
                                            if (self::inArray($item, $new) < 0) {
                                                $new[] = $item;
                                            }
                                        }
                                        //usually boolean
                                        elseif (is_scalar($item)) {
                                            if ($item) {
                                                $new[] = $elem;
                                            }
                                        }
                                        else {
                                            $new = self::unique(array_merge($new, self::makeArray($item)));
                                        }
                                    }
                                }
                            }
                            else {
                                throw new Exception("Unknown psuedo-filter: {$m[2]}, in {$expression}");
                            }

                            //set this for the substr
                            $m[0] = $n[0];
                        }
                        else {
                            throw new Exception("Unknown use of semi-colon: {$m[2]}, in {$expression}");
                        }
                        break;
                    }
                //[attribute="value"] filter
                case "[": {
                        if (preg_match(self::ATTRIBUTES, $expression, $n)) {
                            //change a pipe to a semi-colon for namespace purposes
                            $n[1] = str_replace("|", ":", $n[1]);

                            if (!isset($n[4]) || !$n[4]) {
                                $n[4] = "";
                                $n[6] = null;
                            }

                            if (!isset(self::$attributeFilters[$n[4]])) {
                                print_r($n);
                                //thrown if there is no viable attributeFilter function for the given operator
                                throw new Exception("Unknown attribute filter: {$n[4]}");
                            }

                            if (!$current) {
                                $current = self::makeArray($this->xpath->query("//*"));
                            }

                            foreach ($current as $elem) {
                                if (true === call_user_func(self::$attributeFilters[$n[4]], $elem, $n[1], $n[6], $n, $current)) {
                                    $new[] = $elem;
                                }
                            }

                            //set this for the substr
                            $m[0] = $n[0];
                        }
                        else {
                            //only thrown if query is malformed
                            throw new Exception("Unidentified use of '[' in {$m[0]}");
                        }
                        break;
                    }
                //just a tag - i.e. any descendant of the current context
                default: {
                        $new = $this->tag($m[2], $current);
                        break;
                    }
            }
        }

        return strlen($m[0]) < strlen($expression) ? $this->query(substr($expression, strlen($m[0])), $new, $original_context) : self::unique($new);
    }

    /**
     *	get an element by its id attribute
     *
     *	@access public
     *
     *	@param string $id
     *	@param array $context
     *
     *	@returns array
     */
    public function id ($id, array $context=array()) {
        $new = array();

        //if a context is present - div#id should act like a filter
        if ($context) {
            foreach ($context as $element) {
                if ($element instanceof DOMElement && $element->hasAttribute("id") && $element->getAttribute("id") == $id) {
                    $new[] = $element;
                }
            }

        }
        elseif (($items = $this->xpath->query("//*[@id='{$id}']")) && $items->length > 0) {
            foreach ($items as $item) {
                $new[] = $item;
            }
        }

        return $new;
    }

    /**
     *	get an element by its class attribute
     *
     *	@access public
     *
     *	@param string $id
     *	@param array $context
     *
     *	@returns array
     */
    public function className ($className, array $context=array()) {
        $new = array();

        //if there is a context for the query
        if ($context) {
            //06-08-2009 - added normalize-space function, http://westhoffswelt.de/blog/0036_xpath_to_select_html_by_class.html
            $query = "./descendant-or-self::*[ @class and contains( concat(' ', normalize-space(@class), ' '), ' {$className} ') ]";

            foreach ($context as $elem) {
                if (
                ($items = $this->xpath->query($query, $elem)) &&
                        $items->length > 0
                ) {
                    foreach ($items as $item) {
                        $new[] = $item;
                    }
                }
            }
        }
        //otherwise select any element in the document that matches the selector
        elseif (($items = $this->xpath->query("//*[ @class and contains( concat(' ', normalize-space(@class), ' '), ' {$className} ') ]")) && $items->length > 0) {
            foreach ($items as $item) {
                $new[] = $item;
            }
        }

        return $new;
    }

    /**
     *	get the children elements
     *
     *	@access public
     *
     *	@param string $tag
     *	@param array $context
     *
     *	@returns array
     */
    public function children ($tag="*", array $context=array()) {
        $new = array();

        $query = "./{$tag}";

        //if there is a context for the query
        if ($context) {
            foreach ($context as $elem) {
                if (($items = $this->xpath->query($query, $elem)) && $items->length > 0) {
                    foreach ($items as $item) {
                        $new[] = $item;
                    }
                }
            }
        }
        //otherwise select any element in the document that matches the selector
        elseif (($items = $this->xpath->query($query, $this->document->documentElement)) && $items->length > 0) {
            foreach ($items as $item) {
                $new[] = $item;
            }
        }

        return $new;
    }

    /**
     *	get the adjacent sibling elements
     *
     *	@access public
     *
     *	@param string $tag
     *	@param array $context
     *
     *	@returns array
     */
    public function adjacentSibling ($tag="*", array $context=array()) {
        $new = array();

        $tag = strtolower($tag);

        //if there is a context for the query
        if ($context) {
            foreach ($context as $elem) {
                if ($tag == "*" || strtolower($elem->nextSibling->nodeName) == $tag) {
                    $new[] = $elem->nextSibling;
                }
            }
        }

        return $new;
    }

    /**
     *	get the all sibling elements
     *
     *	@access public
     *
     *	@param string $tag
     *	@param array $context
     *
     *	@returns array
     */
    public function generalSibling ($tag="*", array $context=array()) {
        $new = array();

        //if there is a context for the query
        if ($context) {
            $query = "./following-sibling::{$tag} | ./preceding-sibling::{$tag}";

            foreach ($context as $elem) {
                if (($items = $this->xpath->query($query, $elem)) && $items->length > 0) {
                    foreach ($items as $item) {
                        $new[] = $item;
                    }
                }
            }
        }

        return $new;
    }

    /**
     *	get the all descendant elements
     *
     *	@access public
     *
     *	@param string $tag
     *	@param array $context
     *
     *	@returns array
     */
    public function tag ($tag="*", array $context=array()) {
        $new = array();

        //get all the descendants with the given tagName
        if ($context) {
            $query = "./descendant::{$tag}";

            foreach ($context as $elem) {
                if ($items = $this->xpath->query($query, $elem)) {
                    foreach ($items as $item) {
                        $new[] = $item;
                    }
                }
            }
        }
        //get all elements with the given tagName
        else {
            if ($items = $this->xpath->query("//{$tag}")) {
                foreach ($items as $item) {
                    $new[] = $item;
                }
            }
        }

        return $new;
    }

    /**
     *	A utility function for calculating nth-* style psuedo selectors
     *
     *	@static
     *	@access public
     *
     *	@param DOMNode $context - the element whose position is being calculated
     *	@param string $func - the name of the psuedo function that is being calculated for
     *	@param string $expr - the string argument for the selector
     *	@param DOMXPath $xpath - an existing xpath instance for the document that the context belong to
     *
     *	@returns boolean
     */
    public static function nthChild (DOMNode $context, $func, $expr, DOMXPath $xpath) {
        //remove all the whitespace
        $expr = preg_replace("/\s+/", "", trim(strtolower($expr)));

        //all
        if ($expr == "n" || $expr == "n+0" || $expr == "1n+0" || $expr == "1n") {
            return true;
        }

        //the direction we will look for siblings
        $DIR = (stristr($func, "last") ? "following" : "preceding");

        //do a tagName check?
        $type = stristr($func, "type") ? "[local-name()=name(.)]" : "";

        //the position of this node
        $count = $xpath->evaluate("count( {$DIR}-sibling::*{$type} ) + 1", $context);

        //odd
        if($expr == "odd" || $expr == "2n+1") {
            return $count % 2 != 0;
        }
        //even
        elseif($expr == "even" || $expr == "2n" || $expr == "2n+0") {
            return $count > 0 && $count % 2 == 0;
        }
        //a particular position
        elseif(preg_match("/^([\+\-]?\d+)$/i", $expr, $mat)) {
            $d = (stristr($func, "last") ? -1 : 1) * intval($mat[1]);
            $r = $xpath->query(sprintf("../%s", $type ? $context->tagName : "*"), $context);
            return $r && $r->length >= abs($d) && ($d > 0 ? $r->item($d - 1)->isSameNode($context) : $r->item($r->length + $d)->isSameNode($context));
        }
        //grouped after a particular position
        elseif(preg_match("/^([\+\-]?\d*)?n([\+\-]\d+)?/i", $expr, $mat)) {
            $a = (isset($mat[2]) && $mat[2] ? intval($mat[2]) : 0);
            $b = (isset($mat[2]) && $mat[2] ? intval($mat[2]) : 1);

            return ($a == 0 && $count == $b) ||
                    ($a > 0 && $count >= $b && ($count - $b) % $a == 0) ||
                    ($a < 0 && $count <= $b && (($b - $count) % ($a * -1)) == 0);
        }

        return false;
    }

    /**
     *	A utility function for filtering inputs of a specific type
     *
     *	@static
     *	@access public
     *
     *	@param mixed $elem
     *	@param string $type
     *
     *	@returns boolean
     */
    public static function inputFilter ($elem, $type) {
        //gotta be a DOMNode
        return $elem instanceof DOMNode &&
                //with the tagName input
                strtolower($elem->tagName) == "input" &&
                //and the attribute type
                $elem->hasAttribute("type") &&
                //the attribute type should match the given variable type case insensitive
                trim(strtolower($elem->getAttribute("type"))) == trim(strtolower($type));
    }

    /**
     *	A utility function for making an iterable object into an array
     *
     *	@static
     *	@access public
     *
     *	@param array|Traversable $arr
     *
     *	@return array
     */
    public static function makeArray ($arr) {
        if (is_array($arr)) {
            return array_values($arr);
        }

        $ret = array();

        if ($arr) {
            foreach ($arr as $elem) {
                $ret[] = $elem;
            }
        }

        return $ret;
    }

    /**
     *	A utility function for stripping duplicate elements from an array
     *	works on DOMNodes
     *
     *	@static
     *	@access public
     *
     *	@param array|Traversable $arr
     *
     *	@returns array
     */
    public static function unique ($arr) {
        //first step make sure all the elements are unique
        $new = array();

        foreach ($arr as $current) {
            if (
            //if the new array is empty
            //just put the element in the array
            empty($new) ||
                    (
                    //if it is not an instance of a DOMNode
                    //no need to check for isSameNode
                    !($current instanceof DOMNode) &&
                            !in_array($current, $new)
                    ) ||
                    //do DOMNode test on array
                    self::inArray($current, $new) < 0
            ) {
                $new[] = $current;
            }
        }

        return $new;
    }

    /**
     *	A utility function for determining the position of an element in an array
     *	works on DOMNodes, returns -1 on failure
     *
     *	@static
     *	@access public
     *
     *	@param mixed $elem
     *	@param array|Traversable $arr
     *
     *	@returns integer
     */
    public static function inArray (DOMNode $elem, $arr) {
        $i = 0;

        foreach ($arr as $current) {
            //if it is an identical object or a DOMElement that represents the same node
            if ($current === $elem || ($current instanceof DOMNode && $current->isSameNode($elem))) {
                return $i;
            }

            $i += 1;
        }

        return -1;
    }

    /**
     *	A static function designed to make it easier to get the info
     *
     *	@static
     *	@access public
     *
     *	@param string $query
     *	@param mixed $context
     *	@param array|Traversable $ret - passed by reference
     *
     *	@return array
     */
    public static function find ($query, $context, &$ret=null) {
        $new = array();

        //query using DOMDocument
        if ($context instanceof DOMDocument) {
            $css = new self($context);
            $new = $css->query($query);
        }
        elseif ($context instanceof DOMNodeList) {
            if ($context->length) {
                $css = new self($context->item(0)->ownerDocument);
                $new = $css->query($query, $context);
            }
        }
        //should be an array if it isn't a DOMNode
        //in which case the first element should be a DOMNode
        //representing the desired context
        elseif (!($context instanceof DOMNode) && count($context)) {
            $css = new self($context[0]->ownerDocument);
            $new = $css->query($query, $context);
        }
        //otherwise use the ownerDocument and the context as the context of the query
        else {
            $css = new self($context->ownerDocument);
            $new = $css->query($query, $context);
        }

        //if there is a place to store the newly selected elements
        if ($ret) {
            //append the newly selected elements to the given array|object
            //or if it is an instance of ArrayAccess just push it on to the object
            if (is_array($ret)) {
                $new = self::unique(array_merge($ret, $new));
                $ret = $new;
            }
            elseif (is_object($ret)) {
                if ($ret instanceof ArrayAccess) {
                    foreach ($new as $elem) {
                        $ret[] = $elem;
                    }
                }
                //appending elements to a DOMDocumentFragment is a fast way to move them around
                elseif ($ret instanceof DOMDocumentFragment) {
                    foreach ($new as $elem) {
                        //appendChild, but don't forget to verify same document
                        $ret->appendChild(!$ret->ownerDocument->isSameNode($elem->ownerDocument) ? $ret->ownerDocument->importNode($elem, true) : $elem);
                    }
                }
                //otherwise we need to find a method to use to attach the elements
                elseif (($m = method_exists($ret, "push")) || method_exists($ret, "add")) {
                    $method = $m ? "push" : "add";

                    foreach ($new as $elem) {
                        $ret->$method($elem);
                    }
                }
                elseif (($m = method_exists($ret, "concat")) || method_exists($ret, "concatenate")) {
                    $method = $m ? "concat" : "concatenate";

                    $ret->$method($new);
                }
            }
            //this will save the selected elements into a string
            elseif (is_string($ret)) {
                foreach ($new as $elem) {
                    $ret .= $elem->ownerDocument->saveXML($elem);
                }
            }
        }

        return $new;
    }
}

//this creates the default filters array on the CSSQuery object
CSSQuery::$filters = new RecursiveArrayIterator(array(
        //CSS3 selectors
                "first-child"		=> create_function('$e,$i,$m,$a,$c', 	'return !$e->isSameNode($e->ownerDocument->documentElement) &&
																		$c->xpath->query("../*[position()=1]", $e)->item(0)->isSameNode($e);'),

                "last-child"		=> create_function('$e,$i,$m,$a,$c', 	'return !$e->isSameNode($e->ownerDocument->documentElement) &&
																		$c->xpath->query("../*[last()]", $e)->item(0)->isSameNode($e);'),

                "only-child"		=> create_function('$e,$i,$m,$a,$c', 	'return !$e->isSameNode($e->ownerDocument->documentElement) &&
																		$e->parentNode->getElementsByTagName("*")->length == 1;'),

                "checked"			=> create_function('$e', 				'return strtolower($e->tagName) == "input" && $e->hasAttribute("checked");'),
                "disabled"			=> create_function('$e', 				'return $e->hasAttribute("disabled") && stristr("|input|textarea|select|button|", "|".$e->tagName."|") !== false;'),
                "enabled"			=> create_function('$e', 				'return !$e->hasAttribute("disabled") &&
																		stristr("|input|textarea|select|button|", "|".$e->tagName . "|") !== false &&
																		(!$e->hasAttribute("type") || strtolower($e->getAttribute("type")) != "hidden");'),
                //nth child selectors
                "nth-child"			=> create_function('$e,$i,$m,$a,$c', 	'return CSSQuery::nthChild($e, "nth-child",			$m[3], $c->xpath);'),
                "nth-last-child"	=> create_function('$e,$i,$m,$a,$c', 	'return CSSQuery::nthChild($e, "nth-last-child",	$m[3], $c->xpath);'),
                "nth-of-type"		=> create_function('$e,$i,$m,$a,$c', 	'return CSSQuery::nthChild($e, "nth-of-type",		$m[3], $c->xpath);'),
                "nth-last-of-type"	=> create_function('$e,$i,$m,$a,$c', 	'return CSSQuery::nthChild($e, "nth-last-of-type",	$m[3], $c->xpath);'),

                "first-of-type"		=> create_function('$e,$i,$m,$a,$c', 	'return call_user_func(CSSQuery::$filters["nth-of-type"], 		$e, $i, array(0,1,1,1), $a, $c);'),
                "last-of-type"		=> create_function('$e,$i,$m,$a,$c', 	'return call_user_func(CSSQuery::$filters["nth-last-of-type"],	$e, $i, array(0,1,1,1), $a, $c);'),
                "only-of-type"		=> create_function('$e,$i,$m,$a,$c', 	'return call_user_func(CSSQuery::$filters["first-of-type"], 	$e, $i, $m, 			$a, $c) &&
																		call_user_func(CSSQuery::$filters["last-of-type"], 		$e, $i, $m, 			$a, $c);'),
                //closest thing to the lang filter
                "lang"				=> create_function('$e,$i,$m,$a,$c', 	'return $c->xpath->evaluate(
																				sprintf(
																					"count(./ancestor-or-self::*[@lang and (@lang = \"%s\" or substring(@lang, 1, %u)=\"%s-\")])",
																					$m[3], 
																					strlen($m[3]) + 1, 
																					$m[3]
																				), 
																				$e
																			) > 0;'),

                //negation filter
                "not"				=> create_function('$e,$i,$m,$a,$c', 	'return count($c->query(trim($m[3]), $e)) < 1;'),

                //element has no child nodes
                "empty"				=> create_function('$e', 				'return !$e->hasChildNodes();'),

                //element has child nodes that are elements
                "parent"			=> create_function('$e', 				'return ($n = $e->getElementsByTagName("*")) && $n->length > 0;'),

                //get the parent node of the current element
                "parent-node"		=> create_function('$e,$i,$m,$a,$c', 	'	//if there is no filter just return the first parentNode
																	if (!$m || !isset($m[3]) || !trim($m[3])) return $e->parentNode;
																	//otherwise if the filter is more than a tagName
																	return  preg_match("/^(\*|\w+)([^\w]+.+)/", trim($m[3]), $n) ?  
																			CSSQuery::find(trim($n[2]), $c->xpath->query("./ancestor::{$n[1]}", $e)) :
																			//if the filter is only a tagName save the trouble
																			$c->xpath->query(sprintf("./ancestor::%s", trim($m[3])), $e);'),

                //get the ancestors of the current element
                "parents"			=> create_function('$e,$i,$m,$a,$c', 	'$r = $c->xpath->query("./ancestor::*", $e);
																	return $m && isset($m[3]) && trim($m[3]) ? CSSQuery::find(trim($m[3]), $r) : $r;'),

                //the element has nextSiblings
                "next-sibling"		=> create_function('$e', 				'return ($n = $e->parentNode->getElementsByTagName("*")) && !$n->item($n->length-1)->isSameNode($e);'),

                //the element has previousSiblings
                "previous-sibling"	=> create_function('$e', 				'return !$e->parentNode->getElementsByTagName("*")->item(0)->isSameNode($e);'),

                //get the previousSiblings of the current element
                "previous-siblings"	=> create_function('$e,$i,$m,$a,$c', 	'$r = $c->xpath->query("./preceding-sibling::*", $e);
																return $m && isset($m[3]) && trim($m[3]) ? CSSQuery::find(trim($m[3]), $r) : $r;'),

                //get the nextSiblings of the current element
                "next-siblings"		=> create_function('$e,$i,$m,$a,$c', 	'$r = $c->xpath->query("./following-sibling::*", $e);
																return $m && isset($m[3]) && trim($m[3]) ? CSSQuery::find(trim($m[3]), $r) : $r;'),

                //get all the siblings of the current element
                "siblings"			=> create_function('$e,$i,$m,$a,$c', 	'$r = $c->xpath->query("./preceding-sibling::* | ./following-sibling::*", $e);
																return $m && isset($m[3]) && trim($m[3]) ? CSSQuery::find(trim($m[3]), $r) : $r;'),

                //select the header elements
                "header"			=> create_function('$e', 				'return (bool)preg_match("/^h[1-6]$/i", $e->tagName);'),

                //form element selectors
                "selected"			=> create_function('$e', 				'return $e->hasAttribute("selected");'),

                //any element that would be considered input based on tagName
                "input"				=> create_function('$e', 				'return stristr("|input|textarea|select|button|", "|" . $e->tagName . "|") !== false;'),
                //any input element and type
                "radio"				=> create_function('$e', 				'return CSSQuery::inputFilter($e, "radio");'),
                "checkbox"			=> create_function('$e', 				'return CSSQuery::inputFilter($e, "checkbox");'),
                "file"				=> create_function('$e', 				'return CSSQuery::inputFilter($e, "file");'),
                "password"			=> create_function('$e', 				'return CSSQuery::inputFilter($e, "password");'),
                "submit"			=> create_function('$e', 				'return CSSQuery::inputFilter($e, "submit");'),
                "image"				=> create_function('$e', 				'return CSSQuery::inputFilter($e, "image");'),
                "reset"				=> create_function('$e', 				'return CSSQuery::inputFilter($e, "reset");'),
                "button"			=> create_function('$e', 				'return strtolower($e->tagName) == "button" || CSSQuery::inputFilter($e, "button");'),
                "text"				=> create_function('$e', 				'return strtolower($e->tagName) == "input" &&
																		(
																			//input elements without a type attribute are considered text
																			!$e->hasAttribute("type") || 
																			strtolower($e->getAttribute("type")) == "text"
																		);'),

                //limiting filter
                "has"				=> create_function('$e,$i,$m,$a,$c', 	'return count($c->query($m[3], $e)) > 0;'),

                //text limiting filter
                "contains"			=> create_function('$e,$i,$m,$a,$c', 	'return strstr($e->textContent, preg_replace("/^\s*([\'\"])(.*)\\\\1\s*$/", "\\\\2", $m[3]));'),
                "Contains"			=> create_function('$e,$i,$m,$a,$c', 	'return stristr($e->textContent, preg_replace("/^\s*([\'\"])(.*)\\\\1\s*$/", "\\\\2", $m[3]));'),

                //positional selectors for the current node-set
                "first"				=> create_function('$e,$i', 			'return $i === 0;'),
                "last"				=> create_function('$e,$i,$m,$a', 		'return $i === (count($a) - 1);'),
                "lt"				=> create_function('$e,$i,$m', 			'return $i < $m[3];'),
                "gt"				=> create_function('$e,$i,$m', 			'return $i > $m[3];'),
                "eq"				=> create_function('$e,$i,$m', 			'return $i === intval($m[3]);'),

                //works like nth-child on the currently selected node-set
                "nth"				=> create_function('$e,$i,$m', 			'$expr = preg_replace("/\s+/", "", strtolower(trim($m[3])));
																
																//these selectors select all so dont waste time figuring them out
																if ($expr == "n" || $expr == "n+0" || $expr == "1n+0" || $expr == "1n")
																{
																	return true;
																}
																//even numbered elements
																elseif ($expr == "even" || $expr == "2n" || $expr == "2n+0")
																{
																	return $i % 2 == 0;
																}
																//odd numbered elements
																elseif ($expr == "odd" || $expr == "2n+1")
																{
																	return $i % 2 != 0;
																}
																//positional - a negative position is not supported
																elseif (preg_match("/^([\+\-]?\d+)$/i", $expr, $mat))
																{
																	return $i == intval($mat[1]);
																}
																//grouped according to a position
																elseif (preg_match("/^([\+\-]?\d*)?n([\+\-]\d+)?/i", $expr, $mat))
																{
																	$a = (isset($mat[2]) && $mat[2] ? intval($mat[2]) : 0);
																	$b = (isset($mat[2]) && $mat[2] ? intval($mat[2]) : 1);
																	return ($a == 0 && $i == $b) ||
																			($a > 0 && $i >= $b && ($i - $b) % $a == 0) ||
																			($a < 0 && $i <= $b && (($b - $i) % ($a * -1)) == 0);
																}
																
																return false;
																'),
        ), 2);

//create a default array of attribute filters
CSSQuery::$attributeFilters = new RecursiveArrayIterator(array(
        //hasAttribute and/or attribute == value
                ""	=> create_function('$e,$a,$v=null',	'return $e->hasAttribute($a) && ($v === null || $e->getAttribute($a) == $v);'),
                //!hasAttribute or attribute != value
                "!"	=> create_function('$e,$a,$v', 		'return !$e->hasAttribute($a) || $e->getAttribute($a) != $v;'),
                //hasAttribute and the attribute begins with value
                "^"	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && substr($e->getAttribute($a), 0, strlen($v)) == $v;'),
                //hasAttribute and the attribute ends with value
                '$'	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && substr($e->getAttribute($a), -strlen($v)) == $v;'),
                //hasAttribute and the attribute begins with value . -
                "|"	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && substr($e->getAttribute($a), 0, strlen($v) + 1) == $v."-";'),
                //hasAttribute and attribute contains value
                "*"	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && strstr($e->getAttribute($a), $v);'),

                //special
                //hasAttribute and attribute contains value - case insensitive
                "%"	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && stristr($e->getAttribute($a), $v);'),
                //hasAttribute and the attrributes value matches the given PCRE pattern
                "@"	=> create_function('$e,$a,$v',		'return $e->hasAttribute($a) && preg_match($v, $e->getAttribute($a));'),
        ), 2);

?>
