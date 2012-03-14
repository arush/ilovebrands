<?php

class Ebizmarts_SagePayReporting_Block_Adminhtml_Sagepayreporting_Whitelistip_Whites extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('sagepayreporting/whitelistip/whitelist.phtml');
	}

	public function getValidIPs()
	{
		try{
			return Mage::getModel('sagepayreporting/sagepayreporting')->getValidIPs();
		}catch(Exception $e){
			return $e->getMessage();
		}
	}
}