<?php
/**
 * ClassBuilder
 * A lazy people model object generator, either as output or on the fly.
 *
 * @author Roy Yu
 *
 */
class ClassBuilder {
    private $_className;
    private $_superClass;
    private $_variables;
    private $_methods;
    private $_output;
    private $_filePath;


    public function __construct($className) {
        $this->_className = $className;
        $this->_variables = array();
        $this->_methods = array();
    }

    public function setSuperClass($superClass) {
        $this->_superClass = $superClass;
    }

    public function setVariables($variables) {
        while(list($variableIndex, $variableName) = each($variables)) {
            array_push($this->_variables, $variableName);
        }
    }

    public function setMethods($methods) {
        while(list($methodIndex, $methodName) = each($methods)) {
            array_push($this->_methods, $methodName);
        }
    }

    public function getOutput() {
        return $this->_output;
    }

    function getFilePath() {
        return $this->_filePath;
    }

    public function generateClass() {
        $Output = '';
        $Output .= "<?php\n";
        $Output .= "\n";

        if($this->_superClass) {
            $Output .= $this->tab(1) . "class $this->_className extends $this->_superClass {\n";
        }
        else {
            $Output .= $this->tab(1) . "class $this->_className {\n";
        }

        reset($this->_variables);
        while(list($VarIndex, $VarName) = each($this->_variables)) {
            $Output .= $this->tab(2) . "private \$_$VarName = null;\n";
            /*
            $tmp .= "\$$VarName";
            
            if((count($this->Variables) - $VarIndex) > 1) {
            	$tmp .= ', ';
            }
            */
        }

        $Output .= "\n";

        $Output .= $this->tab(2) . "// Class constructor\n";
        $Output .= $this->tab(2) . "function __construct() {\n";
        $Output .= $this->tab(3) . "return \$this; \n";
        $Output .= $this->tab(2) . "}\n";
        $Output .= "\n";

        $Output .= $this->tab(2) . "// Returns class name\n";
        $Output .= $this->tab(2) . "function getClassName() {\n";
        $Output .= $this->tab(3) . "return '$this->_className';\n";
        $Output .= $this->tab(2) . "}\n";
        $Output .= "\n";

        reset($this->_methods);
        while(list($MethodIndex, $MethodName) = each($this->_methods)) {
            $Output .= $this->tab(2) . "public function $MethodName() {\n";
            $Output .= "\n";
            $Output .= $this->tab(2) . "}\n";
            $Output .= "\n";
        }

        reset($this->_variables);
        while(list($VarIndex, $VarName) = each($this->_variables)) {
            $Output .= $this->tab(2) . "public function set".ucwords($VarName)."(\$$VarName = null) {\n";
            $Output .= $this->tab(3) . "\$this->$VarName = \$$VarName;\n";
            $Output .= $this->tab(3) . "return \$this; \n";
            $Output .= $this->tab(2) . "}\n";
            $Output .= "\n";
        }

        reset($this->_variables);
        while(list($VarIndex, $VarName) = each($this->_variables)) {
            $Output .= $this->tab(2) . "public function get".ucwords($VarName)."() {\n";
            $Output .= $this->tab(3) . "return \$this->$VarName;\n";
            $Output .= $this->tab(2) . "}\n";
            $Output .= "\n";
        }

        $Output .= "\n";



        $Output .= "\n";
        $Output .= $this->tab(1) . "}\n";
        $Output .= '?>';

        $this->_output = $Output;
        $this->_filePath = $FilePath;
    }

    public function tab($num) {
        $output = '';

        for($i = 1; $i <= $num; $i++) {
            $output .= '   ';
        }

        return $output;
    }
}
?>