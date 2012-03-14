<?php

/**
 * SagePay PayPal main model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_SagePayPayPal extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'sagepaypaypal';

    protected $_formBlockType = 'sagepaysuite/form_sagePayPayPal';
    protected $_infoBlockType = 'sagepaysuite/info_sagePayPayPal';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    public function isAvailable($quote = null)
    {
    	return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    //public function getCheckoutRedirectUrl()
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('sgps/paypalexpress/go', array('_secure' => true));
    }

}