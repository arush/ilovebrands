<?php

/**
 * Checkout helper
 *
 * @category    Ebizmarts
 * @package     Ebizmarts_SagePaySuite
 * @author      Ebizmarts Team <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Helper_Checkout extends Mage_Core_Helper_Abstract
{

	public function getOnepage()
	{
		return Mage::getSingleton('checkout/type_onepage');
	}

	public function isMultiShippingOverview()
	{
		$request = Mage::app()->getRequest();

		return (bool)('/checkout/multishipping/overview/' === $request->getRequestString());
	}

    public function deleteQuote()
    {
        if($this->getOnepage()->getQuote()->hasItems())
        {
            try {
                $this->getOnepage()->getQuote()->setIsActive(false)
                ->save();
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
    }
}