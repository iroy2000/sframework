<?php
/**
 * Router
 * Each user request will be converted to a Router object in order to get
 * specific Controller for process
 *
 * @author Roy Yu
 *
 */
class Router {
    protected $_controller = null;
    protected $_action = null;
    protected $_id = null;
    protected $_extra = array();
    protected $_post = array();
    protected $_query = null;

    // constructor
    public function __construct($request = null) {
        $this->_query = htmlspecialchars($request);
        $this->init();
    }

    // get referer
    public static function getReferer() {
        $array = explode(".com/", $_SERVER['HTTP_REFERER']);
        return explode("".Globals::getConfig()->cmd->delimiter."", $array[1]);
    }

    // get controller
    public function getController($suffix = false) {
        if($suffix)
            return ucwords($this->_controller) . Globals::getConfig()->controller->suffix;

        return $this->_controller;
    }

    // get action
    public function getAction($fillWhenBlank = false, $defaultAction = 'default') {
        if($this->_action == '' && $fillWhenBlank)
            return $defaultAction;
        else
            return $this->_action;
    }

    // get third parameter   :controller/:action/:id
    public function getId() {
        return $this->_id;
    }

    // get forth parameters :controller/:action/:id/:extra
    // extra can be any format, as long as it is after 3rd (id)
    public function getExtra() {
        return $this->_extra;
    }

    // return $_POST
    public function getPost() {
        if(isset($_POST)) {
            //$args = array();
            //$this->_post = $this->filter($_POST, $args);
            $this->_post = $_POST;
        }
        return $this->_post;
    }

    // get query string
    public static function getQuery($request = null) {
        if($request == null) {
            $query = htmlspecialchars($_SERVER['QUERY_STRING']);
            return explode("".Globals::getConfig()->cmd->delimiter."", $query);
        }

        return explode("".Globals::getConfig()->cmd->delimiter."", $request);
    }

    /**
     * All initialization goes in this function
     */
    public function init() {
        $array = Router::getQuery($this->_query);

        // This part is not implement yet, please see function description
        $filtered = $this->filter($array);

        $this->defaultRule($filtered);
    }

    /**
     * Divides User request into list of commands
     * For example, test/update/1 ( controller / action / id )
     * You can overwrite this rule
     */
    public function defaultRule($array = array()) {
        if(isset($array[0]))
            $this->_controller = $array[0];
        if(isset($array[1]))
            $this->_action = $array[1];
        if(isset($array[2]))
            $this->_id = $array[2];
        if(isset($array[3])) {
            $this->_extra = array_slice($array, 3, count($array) - 1);
        }
    }

    /**
     * Any validation goes in this function
     */
    public function validate() {

    }

    /**
     * Filter input
     * @param $input Items want to filter
     * @param $arg filter argument
     * @Doc http://us3.php.net/manual/en/function.filter-input-array.php
     */
    public function filter($input = NULL, $args = NULL) {
        if(!($input[0] == null || $input[0] == '' || $args == null))
            return filter_input_array($input, $args);

        return $input;
    }
}
?>