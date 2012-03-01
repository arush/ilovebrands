<?php

/**
 * Paypal express payment controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_PaypalexpressController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;
    protected $_checkout = null;

    /**
     * SagePaySuite session instance getter
     *
     * @return Ebizmarts_SagePaySuite_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('sagepaysuite/session');
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

	public function getPaypalTrnModel()
	{
		return Mage::getModel('sagepaysuite2/sagepaysuite_paypaltransaction');
	}


    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Make sure addresses will be saved without validation errors
     */
    private function _ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    private function _initCheckout()
    {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || !Mage::getModel('sagepaysuite/api_payment')->isPayPalEnabled()) {

            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('sagepaysuite')->__('Unable to initialize Express Checkout.'));

        }/*
        	COMMENTED PER Ignacio's request
        else if(!$quote->getShippingAddress()->getShippingMethod()){

        	$this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
			Mage::throwException(Mage::helper('sagepaysuite')->__('Please get a shipping estimate. See below, "Estimate Shipping and Tax"'));

        }*/

        $this->_checkout = Mage::getSingleton('sagepaysuite/paypal_checkout', array(
            'quote'  => $quote,
        ));
    }

	/**
	 * Post transaction to SagePay - PayPal
	 */
	public function goAction()
	{
		try {

			$this->_initCheckout();
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (!($this->_quote->getCustomerId()) && $customer && $customer->getId()) {
                $this->_checkout->setCustomer($customer);
            }

			$rs = $this->_checkout->start();

			if(is_string($rs)){
				$this->_redirectUrl($rs);
				return;
			}

        }catch (Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            Ebizmarts_SagePaySuite_Log::we($e);
        }
        $this->_redirect('checkout/cart', array('_secure'=>true));
        return;
	}

    /**
     * Update shipping method (combined action for ajax and regular request)
     */
    public function saveShippingMethodAction()
    {
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->loadLayout('sagepaysuite_paypalexpress_review_details');
                $this->getResponse()->setBody($this->getLayout()->getBlock('root')
                    ->setQuote($this->_getQuote())
                    ->toHtml());
                return;
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to update shipping method.'));
            Mage::logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody('<script type="text/javascript">window.location.href = '
                . Mage::getUrl('sgps/paypalexpress/review', array('_secure'=>true)) . ';</script>');
        } else {
            $this->_redirect('sgps/paypalexpress/review', array('_secure'=>true));
        }
    }

    /**
     * Submit the order
     */
    public function placeOrderAction()
    {
        try {
            $this->_initCheckout();
            $this->_checkout->place();

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }
            }

            $this->_redirect('checkout/onepage/success', array('_secure'=>true));
            return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to place the order. %s', $e->getMessage()));
            Mage::logException($e);
        }
        $this->_redirect('sgps/paypalexpress/review', array('_secure'=>true));
    }

	public function callbackAction()
	{
		$_r = $this->getRequest();

		if(!$_r->isPost()){
			$this->_redirect('/');
			return;
		}

		$sessionVendorTx = Mage::getModel('sagepaysuite/api_payment')->getSageSuiteSession()->getLastVendorTxCode();

		$postArray = $_r->getPost();

		$postArray = array_map(array($this, 'encodechars'), $postArray);

		$postArray = Mage::helper('sagepaysuite')->arrayKeysToUnderscore($postArray);

		$trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
            ->loadByVendorTxCode($sessionVendorTx);

		$this->getPaypalTrnModel()
		->loadByVendorTxCode($sessionVendorTx)
		->setTransactionId($trn->getId())
		->addData($postArray)
		->setVendorTxCode($sessionVendorTx)
		->setVpsProtocol($_r->getPost('VPSProtocol'))
		->setCustomerEmail($_r->getPost('CustomerEMail'))
		->setPayerId($_r->getPost('PayerID'))
		->setVpsTxId($_r->getPost('VPSTxId'))
		->setDeliveryAddress($_r->getPost('DeliveryAddress1'))
		->setDeliveryAddresss($_r->getPost('DeliveryAddress2'))
		->setTrndate(Mage::getModel('sagepaysuite/api_payment')->getDate())
		->save();

		if($_r->getPost('Status') != Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_PAYPAL_OK){
        	$this->_getCheckoutSession()->addError($_r->getPost('StatusDetail'));
        	$this->_redirect('checkout/cart');
        	return;
		}

		$this->_initCheckout();

		$this->_checkout->returnFromPaypal($_r);

		Sage_Log::log($_r->getPost(), null, 'PayPalCallback.log');

		$this->_getSession()->setSagepaypaypalRqpost($_r->getPost());

        $this->_redirect('sgps/paypalexpress/review', array('_secure'=>true));
        return;

	}

    /**
     * Review order after returning from PayPal
     */
    public function reviewAction()
    {
        try {

            $this->_initCheckout();

            $this->_checkout->prepareOrderReview();

            $this->loadLayout();
            $this->_initLayoutMessages('sagepaysuite/session');
            $this->getLayout()->getBlock('sagepaypaypal.express.review')
                ->setQuote($this->_getQuote())
                ->getChild('details')->setQuote($this->_getQuote())
            ;
            $this->renderLayout();
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to initialize Express Checkout review.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart', array('_secure'=>true));
    }

    public function encodechars($str)
    {
		return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-15');
    }

}