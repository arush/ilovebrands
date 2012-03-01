<?php
class Arush_Oneall_Helper_Identifiers extends Mage_Core_Helper_Abstract {

	/**
	 * Assigns a new identifier to a customer
	 *
	 * @param int $customer_id
	 * @param string $identifier
	 */
	public function save_identifier($customer_id, $profile, $customer = false) {

		/**
		 * Make sure we have a valid customer_id
		 *
		 */

		if ($customer === false) {
			$customer = Mage::getModel('customer/customer')->load($customer_id);
		}

		if(!$customer->getId())
			Mage::throwException('Invalid Customer ID');
			
		if ($customer->getOneallUserToken() != $profile['user_token']) {
			$customer->setOneallUserToken($profile['user_token']);		
			$customer->save();
		}	
//		$customer->setOneallUserToken($profile['identity_token']);
		// below line just doesn't work, can't understand why
		//	$customer->setOnealluuid($profile['oneall_uuid'])
		//		->save();
		
		
		/**
		 * Make the save
		 *
		 */
		try {
			Mage::getModel('oneall/identifiers')
					->setIdentifier($profile['identifier'])
					->setIdentityToken($profile['identity_token'])
					->setProvider($profile['provider'])
					->setProfileName($profile['profile_name'])
					->setCustomerId($customer_id)
					->save();
			
		}
		catch (Exception $e) {
			echo "Could not save: " . $e->getMessage() . "\n";
		}

	}

	/**
	 * Gets a customer by identifier
	 *
	 * @param string $identifier
	 * @return Mage_Customer_Model_Customer
	 */
	public function get_customer($identity_token) {
		$customer_id = Mage::getModel('oneall/identifiers')
						->getCollection()
						->addFieldToFilter('identity_token', $identity_token)
						->getFirstItem();

		$customer_id = $customer_id->getCustomerId();
		if((int) $customer_id > 0) {
			$customer = Mage::getModel('customer/customer')
						->getCollection()
						->addFieldToFilter('entity_id', $customer_id)
						->getFirstItem();
			return $customer;
		}

		return false;
	}
	
	public function get_customer_from_user_token($user_token) {
		
		$customerCollection = Mage::getModel('customer/customer')
			->getCollection()
			->addAttributeToSelect('oneall_user_token')
			->addAttributeToFilter('oneall_user_token', $user_token)
			->getFirstItem();

		
		$customer_id = $customerCollection->getCustomerId();
		
		if((int) $customer_id > 0) {
			return $customerCollection;
		}

		return false;
	}


	public function get_identifiers($customer_id) {
		if((int) $customer_id > 0){
			$identifiers = Mage::getModel('oneall/identifiers')
							->getCollection()
							->addFieldToFilter('customer_id', $customer_id);

			return $identifiers;
		}

		return false;
	}

	public function delete_identifier($id) {
		$customer_id = Mage::getSingleton('customer/session')
				->getCustomer()
				->getId();

		$identifier = Mage::getModel('oneall/identifiers')
							->getCollection()
							->addFieldToFilter('identity_token', $id)
							->getFirstItem();
		if($identifier->getCustomerId() == $customer_id) {
			try {
				$identifier->delete();
			} catch (Exception $e) {
				echo "Could not delete: $e";
			}
		}
		
	}

}