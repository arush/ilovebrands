<?php

class Arush_Oneall_Model_Observer {

	public function addIdentifier($observer) {
	
    
		if($profile = Mage::getSingleton('oneall/session')->getIdentifier()) {
			Mage::helper('oneall/identifiers')
				->save_identifier($observer->getCustomer()->getId(), $profile, $observer->getEvent()->getCustomer());
			Mage::getSingleton('oneall/session')->setIdentifier(false);
		}
	}
	

}