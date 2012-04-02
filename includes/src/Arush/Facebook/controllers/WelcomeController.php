<?php

class Arush_Facebook_WelcomeController extends Mage_Core_Controller_Front_Action
{
    public function landingAction(){
    $this->loadLayout();
    $this->renderLayout();
    }
}