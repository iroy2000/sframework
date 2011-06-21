<?php
class IndexController extends Controller {

    public function setup() {
        $this->setAllowActions(array('default','test'));
        $this->setView(Globals::getConfig()->template->path . 'index', null);
        $this->setCachableActions(array('default'));
    }

    public function defaultAction() {

    }
}