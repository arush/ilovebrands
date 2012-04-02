<?php

class Arush_Oneall_IndexController extends Mage_Core_Controller_Front_Action {

    public function _construct() {
        // Change to the default store so we
        // can grab the frontend user session
        //Mage::app()->setCurrentStore('default');

        // Get the customer session
        //$this->_session = Mage::getSingleton('customer/session');
    }

    public function indexAction() {

//        $rpdata = Mage::helper('oneall/rpxcall')->rpxLookupRpCall();
//        var_dump($rpdata);
//        echo($rpdata);

        $rp_moredata = Mage::helper('oneall/rpxcall')->rpxUiConfigCall();
//        $rp_moredata = "test";
        echo($rp_moredata);

    }


}