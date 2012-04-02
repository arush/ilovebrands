<?php

/**
 * REWRITE Mage_Payment Helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Helper_Payment_Data extends Mage_Payment_Helper_Data
{
    /**
     * Retrieve all payment methods
     *
     * @param mixed $store
     * @return array
     */
    public function getPaymentMethods($store = null)
    {
        $_methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store);

        if(isset($_methods['sagepaysuite'])){
        	unset($_methods['sagepaysuite']);
        }
        return $_methods;
    }
}