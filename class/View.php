<?php
/**
 * View
 * Hold information of things related to view and template
 *
 * @author Roy Yu
 *
 */
class View {
    private $_view    = null;
    private $_helper  = null;
    private $_viewMap = null;
    private $_ext = null;
    private $_layout = true;

    // constructing view object
    public function __construct($view = null, $helper = null, $layout = true, $ext = '.php') {
        $this->_view = $view;
        $this->_helper = $helper;
        $this->_ext = $ext;
        $this->_layout = $layout;
        $this->_viewMap = new stdClass();
    }

    public function setTemplate($template = null) {
        $this->_view = $template;
    }

    public function setHelper($helper = null) {
        $this->_helper = $helper;
    }

    public function setLayout($layout = null) {
        $this->_layout = $layout;
    }

    /**
     * function setViewObject
     * You can set any kinds of parameter in this object to modify the contents of the view
     *
     * @param String $key
     * @param String $value
     */
    public function setViewObject($key = null, $value = null) {
        if($key != null &&  $value != null) {
            $this->_viewMap->{$key} = $value;
        }
        return $this;
    }

    /**
     * function setViewObjects
     * You can set any kinds of parameter in this object to modify the contents of the view
     *
     * @param Array $items ( with key, value pair )
     */
    public function setViewObjects($items = null) {
        if($items != null) {
            foreach($items as $key => $value) {
                $this->_viewMap->{$key} = $value;
            }
        }
        return $this;
    }

    public function getViewObject() {
        return $this->_viewMap;
    }

    /**
     * Check if this action needs view
     * @return <string>
     */
    public function hasView() {
        return $this->_view != null;
    }

    /**
     * Check if this action needs helper
     * @return <string>
     */
    public function hasHelper() {
        return $this->_helper != null;
    }

    /**
     * Include the contents from the action
     * @param <string> $action
     */
    public function getContent($action, $view = null) {
        require_once($this->_view.$action.$this->_ext);
    }

    /**
     * Fetch front end file
     * You can pass object that can share with template
     * @param Controller $controller
     */
    public function dispatch($controller = null) {

        if($this->_view != null && trim($this->_view) != '') {

            $view = $this->getViewObject();

            if($controller->getRouter() != null)
                $action = $controller->getRouter()->getAction();
            else
                $action = null;

            if($action != null || trim($action) != '') {
                if(!in_array($action, $controller->getAllowActions())) {
                    $action = '';
                } else {
                    $action = '_'.$action;
                }
            }

            if($this->_layout == false) {
                $this->getContent($action, $view);
            } else {
                if($this->hasHelper())
                    require_once($this->_helper.$this->_ext);

                try {
                    $layout = $this->_layout !== true ? $this->_layout : Globals::getConfig()->template->layout;
                    require_once($layout);
                }catch(Exception $e) {
                    
                    $this->getContent($action);
                }
            }
        }
    }

    public static function getJs($view = null) {
        if(empty($view->js))
            return false;

        foreach($view->js as $item) {
            echo '<script type="text/javascript" src="'.$item.'"></script>';
        }
    }

    // this is for you to place before body tag if needed
    public static function getEndingJs($view = null) {
        if(empty($view->ending_js))
            return false;

        foreach($view->ending_js as $item) {
            echo '<script type="text/javascript" src="'.$item.'"></script>';
        }
    }

    public static function getCss($view = null) {
        if(empty($view->css))
            return false;

        foreach($view->css as $item) {
            echo '<link rel="stylesheet" href="'.$item.'" />';
        }
    }
}
?>