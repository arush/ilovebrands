<?php

/**
 * Token payment form
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Form_SagePayToken extends Mage_Payment_Block_Form_Cc
{

	public function getTokenCards()
	{
		return $this->helper('sagepaysuite/token')->loadCustomerCards();
	}

	public function canUseToken()
	{
		return Mage::getModel('sagepaysuite/sagePayToken')->isEnabled();
	}

}