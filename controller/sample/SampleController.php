<?php
// this is a sample of a controller
class SampleController extends Controller {

    // initial setup
    public function setup() {
        // Required. Only the actions you put here exposed to other users
        $this->setAllowActions(array('default','test'));

        // Optional. Overwrite the default behavior
        // 1st param is which template to use
        // 2nd param is if you need helper
        // 3rd param is if you need layout for this actions
        $this->setView(Globals::getConfig()->template->path . $this->getRouter()->getController(),
                Globals::getConfig()->template->helper . $this->getRouter()->getController(), false);

        // Optional.  Define which actions are cacable
        $this->setCachableActions(array('default'));
    }

    public function defaultAction() {
        // your logic here
    }

    public function testAction() {
        // your logic here
    }

    public function otherAction() {
        // this function won't get run, because you didn't set it in setAllowActions().
    }
}