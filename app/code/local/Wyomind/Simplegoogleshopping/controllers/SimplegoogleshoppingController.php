<?php

class Wyomind_Simplegoogleshopping_SimplegoogleshoppingController extends Mage_Core_Controller_Front_Action {

    public function generateAction() {
        // http://www.example.com/index.php/simplegoogleshopping/simplegoogleshopping/generate/id/{data_feed_id}/ak/{YOUR_ACTIVATION_KEY}
        
        $id = $this->getRequest()->getParam('id');
        $ak=$this->getRequest()->getParam('ak');
        
        $activation_key=Mage::getStoreConfig("simplegoogleshopping/license/activation_key");
        
        if($activation_key==$ak) {


            $simplegoogleshopping = Mage::getModel('simplegoogleshopping/simplegoogleshopping');
            $simplegoogleshopping->setId($id);
            if ($simplegoogleshopping->load($id)) {
                try {
                    $simplegoogleshopping->generateXml();
                    die(Mage::helper('simplegoogleshopping')->__('The data feed "%s" has been generated.', $simplegoogleshopping->getSimplegoogleshoppingFilename()));
                } catch (Mage_Core_Exception $e) {
                    die($e->getMessage());
                } catch (Exception $e) {
                    die($e->getMessage());
                }
            } else {
                die(Mage::helper('simplegoogleshopping')->__('Unable to find a data feed to generate.'));
            }
        } else die('Invalid activation key');
    }

}

