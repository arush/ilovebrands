<?php
class Vdh_Randomquote_Model_Observer {

	public function customerSave($observer) {
		$customer = $observer->getEvent()->getCustomer();	
		return Mage::getModel('randomquote/customer')->setGroupId($customer);
	}
	
	public function orderSave($observer) {
		$order = $observer->getEvent()->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		return Mage::getModel('randomquote/customer')->setGroupId($customer);
	}	

	public function profileSave($observer) {
		$customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
		return Mage::getModel('randomquote/customer')->setGroupId($customer);
	}	
	
}