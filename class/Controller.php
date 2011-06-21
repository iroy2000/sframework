<?php
/**
 * Abstract Controller
 * The base of controllers in this framework, it mainly process
 * user request with database, and then push them to view.
 *
 * @author Roy Yu
 *
 */
abstract class Controller {
    
    protected $_view   = null;  // this will hold a View instance
    protected $_result = null;
    protected $_error  = null;
    protected $_cmd    = null;
    protected $_router = null;  // this will hold a Router instance
    protected $_allowActions = array();
    protected $_cacheActions = array();


    public function __construct(Router $router = null) {

        if($router == null) {
            throw new Exception(__CLASS__ . ' Router is null');
        }

        $this->setRouter($router);
        $this->setDefaultView();
        $this->setup();
        $this->process();
    }

    /**
     * Check if it is a Post
     *
     * @return boolean
     */
    public function isPost() {
        return isset($_POST) && count($_POST) > 0;
    }

    /**
     * Check if controllers has Error
     *
     * @return boolean
     */
    public function hasError() {
        if($this->_error != null) {
            return true;
        }
        return false;
    }

    /**
     * Default behavior of view ( create default view object )
     */
    private function setDefaultView() {
        $page = 'index';

        if($this->getRouter()->getController() != null) {
            $page = $this->getRouter()->getController();
        }

        $template =  Globals::getConfig()->template->path . $page;
        $this->_view = new View($template,null, true);

        try {
            $this->setJs();
            $this->setCss();
        } catch (Exception $e) {
            $this->defaultAction();
        }
    }

    /**
     * User can overwrite default view object
     *
     * @param String $template
     * @param String $viewHelper
     * @param String $layout
     */
    public function setView($template = null, $viewHelper = null, $layout = true) {

        if($template != null)
            $this->_view->setTemplate($template);

        if($viewHelper != null)
            $this->_view->setHelper($viewHelper);

        $this->_view->setLayout($layout);
    }

    /**
     * Set controller return result ( pending )
     *
     * @param String $result
     */
    public function setResult($result = null) {
        $this->_result = $result;
    }

    public function setError($error = null) {
        $this->_error = $error;
    }

    public function setRouter($router = null) {
        $this->_router = $router;
    }

    public function setAllowActions($actions = array()) {
        $this->_allowActions = $actions;
    }

    public function setCachableActions($actions = array()) {
        $this->_cacheActions = $actions;
    }

    public function getError() {
        return $this->_error;
    }

    public function getResult() {
        return $this->_result;
    }

    public function getAllowActions() {
        return $this->_allowActions;
    }

    public function getCacheActions() {
        return $this->_cacheActions;
    }

    /**
     * function getView()
     * Controller getView() returns View instance
     *
     * @return View $_view
     */
    public function getView() {
        return $this->_view;
    }

    /**
     * function getRouter()
     * @return Router $_router
     */
    public function getRouter() {
        return $this->_router;
    }

    /**
     * A dispatch function of View Object which fetch front end file
     *
     */
    public function dispatch() {
        $cachable = false;

        if(in_array($this->getRouter()->getAction(true), $this->getCacheActions())) {
            $cachable = true;
        }

        $cache = FileCache::getFileCacheInstance($this->getRouter(), 3600)->init($cachable);
        $this->getView()->dispatch($this);
        $cache->save();
    }

    /**
     * Default behavior: show default when action not found
     * But user are free to overwrite this rule inside concreteController
     */
    public function process() {
        if(!in_array($this->getRouter()->getAction(), $this->getAllowActions()) || $this->getRouter()->getAction() == null) {
            $this->defaultAction();
        } else {
            $customAction = $this->getRouter()->getAction().Globals::getConfig()->action->suffix;
            $this->$customAction();
        }
    }

    /**
     * User can overwrite this one if they have javascript for a particular actions
     * @param <type> $list | a list of javascript (with path) to pass in
     */
    public function setJs($list = array()) {
        $this->getView()->setViewObject('js', $list);
    }

    /**
     * User can overwrite this one if they have style sheets for a particular actions
     * @param <type> $list | a list of css (with path) to pass in
     */
    public function setCss($list = array()) {
        $this->getView()->setViewObject('css', $list);
    }


    /**
     * Abstract functions for childern controllers to implement
     *
     */
    abstract function setup(); // setup
    abstract function defaultAction(); // children must at least have a defaultAction

}
?>