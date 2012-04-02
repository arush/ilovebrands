<?php
class Vdh_Randomquote_Model_Customer extends Varien_Object {

	public function setGroupId($customer = false) {
		if ($customer === false) {
			$customerId = Mage::getSingleton('customer/session')->getId();		
			$customer = Mage::getModel('customer/customer')->load($customerId);
		}
		$orders = Mage::getModel('sales/order')
			->getCollection()
			->addAttributeToFilter('customer_id', $customer->getId());

		$profiles = Mage::getModel('sales/recurring_profile')
			->getCollection()
			->addFieldToFilter('customer_id', $customer->getId())
			->addFieldToFilter('state', 'active');
			
		$skus = array();
		foreach($profiles as $profile) {
			$orderItemInfo = unserialize($profile->getOrderItemInfo());
			$skus[] = $orderItemInfo['sku'];
		}
		
		$relations = unserialize(Mage::getStoreConfig('randomquote/general/plans'));
		
		$priority = 0;
		$customerGroupId = 1;
		foreach($relations as $relation) {
			if (in_array($relation['sku'], $skus) && $relation['priority'] > $priority) {
				$priority = $relation['priority'];
				$customerGroupId = $relation['customer_group_id'];
			}
		}
		if ($customerGroupId != $customer->getGroupId()) {
			$customer->setGroupId($customerGroupId);
			$customer->setCustomerGroupId($customerGroupId);
			$customer->save();	
		}	
		return true;
	}
}