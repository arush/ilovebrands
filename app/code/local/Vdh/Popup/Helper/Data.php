<?php
class Vdh_Popup_Helper_Data extends Mage_Core_Helper_Abstract {


	public function getTriggerUrl() {
		return Mage::getStoreConfig('popup/general/trigger');
	}
	
	public function getUrls() {
		$urls = Mage::getStoreConfig('popup/general/url');
		if (!$urls) { return array(); }
		$return = array();
		
		$urls = unserialize($urls);
		foreach($urls as $url) {
			$return[$url['sort_order']] = $url['url'];
		}
		ksort($return);
		return $return;
	}
	
	public function getDelay() {
		return (int)Mage::getStoreConfig('popup/general/delay') * 1000;
	}
}