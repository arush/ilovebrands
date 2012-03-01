<?php

class Vdh_Customerurl_Helper_Data extends Mage_Core_Helper_Abstract {


	public function getMappings() {
	
		$mappings = Mage::getStoreConfig('customerurl/general/mappings');
		return unserialize($mappings);
	
	}
	
}