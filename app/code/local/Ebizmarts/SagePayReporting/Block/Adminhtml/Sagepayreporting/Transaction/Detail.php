<?php

class Ebizmarts_SagePayReporting_Block_Adminhtml_SagePayReporting_Transaction_Detail extends Mage_Adminhtml_Block_Widget
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('sagepayreporting/transaction/detail.phtml');
	}

	public function getHeaderText()
	{
		return Mage::helper('sagepayreporting')->__('Get Transaction Details');
	}

	public function getSaveUrl()
	{
		return $this->getUrl('*/*/*');
	}

	public function getTransactionDetail()
	{
		return Mage::registry('sagepay_detail');
	}

}