<?php
class Vdh_Customerurl_Model_Customer {

	public function addRewrites($customer) {

		$helper = Mage::helper('customerurl');
		
		$templates = $helper->getMappings();
		foreach ($templates as $template) {
			$url = str_replace(
				array('{customer_id}', '{customer_url}'), 
				array($customer->getId(), $customer->getCustomerurl()), 
				$template['url']
			);
			$redirect = str_replace(
				array('{customer_id}', '{customer_url}'), 
				array($customer->getId(), $customer->getCustomerurl()), 
				$template['redirect']
			);
			$currentUrl = Mage::getModel('core/url_rewrite')->getCollection()
			->addFilter('target_path', $redirect)
			->addFilter('id_path', $redirect . '_' . $customer->getId())
			->getFirstItem();
/*
			$sql = "DELETE FROM ".Mage::getSingleton('core/resource')->getTableName('core_url_rewrite')." WHERE id_path = '".$redirect . '_' . $customer->getId()."' AND target_path = '".$redirect."'";
			$conn = Mage::getSingleton("core/resource")->getConnection("core_write");
			$conn->query($sql);
*/
//			$currentUrl = Mage::getModel('core/url_rewrite');
			$currentUrl->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
			$currentUrl->setIdPath($redirect . '_' . $customer->getId());
			$currentUrl->setRequestPath($url);						
			$currentUrl->setTargetPath($redirect);			
			$currentUrl->setIsSystem(false);						
			$currentUrl->save();
		}
		return true;
	}
	
	public function createUnique($customer, $default = 'username') {
		if (!$customer->getCustomerurl()) {
			$temp = strtolower($customer->getFirstname()) . strtolower($customer->getLastname());
			$temp = trim($temp);
			$temp = preg_replace("/[^a-zA-Z0-9_-]/", "", $temp);						
			$customer->setCustomerurl($temp);
			
		}
		if (!$customer->getCustomerurl()) {
			$customer->setCustomerurl($default);
		}
		
		$updatedUrl = $customer->getCustomerurl();	

		while(
			Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('customerurl', $updatedUrl)
				->addAttributeToFilter('entity_id', array('neq' => ($customer->getId()) ? $customer->getId() : 0))
				->count() > 0
		) {
			
			$originalUrl	= $updatedUrl;
			$pattern		=	"/(.*)([0-9]+)$/";

			$updatedUrl		=	preg_replace_callback(
									$pattern,
									create_function(
						            	'$matches',
			        			    	'return ($matches[1] . (((int)$matches[2])+1));'
							        ),
						    	    $originalUrl
								);
			if ($updatedUrl == $originalUrl) { $updatedUrl .= '1'; }
											
		}

		$customer->setCustomerurl(strtolower($updatedUrl));	
		return true;				
	}
	
}