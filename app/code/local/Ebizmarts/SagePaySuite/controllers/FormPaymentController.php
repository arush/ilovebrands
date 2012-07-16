<?php

/**
 * SagePay FORM controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_FormPaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;

	public function getOnepage()
	{
		return Mage::getSingleton('checkout/type_onepage');
	}

    /**
     * SagePaySuite session instance getter
     *
     * @return Ebizmarts_SagePaySuite_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('checkout/session');
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

	private function getFormModel() {
		return Mage::getModel('sagepaysuite/sagePayForm');
	}

	protected function _getTransaction()
	{
		return Mage::getModel('sagepaysuite2/sagepaysuite_transaction');
	}

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    private function _initCheckout()
    {
        $quote = $this->_getQuote();

        if (!$quote->hasItems() || (int)$this->getFormModel()->getConfigData('active') !== 1) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('sagepaysuite')->__('Unable to initialize FORM Checkout.'));
        }
    }

	public function saveOrderAction()
	{

		Mage::helper('sagepaysuite')->validateQuote();

		$this->_initCheckout();

		$resultData             = array();
		$resultData['success']  = true;
		$resultData['error']    = false;
		$resultData['redirect'] = Mage::getUrl('sgps/formPayment/go', array('_secure' => true));

		return $this->getResponse()->setBody(Zend_Json::encode($resultData));
	}

	/**
	 * Post to SagePay form
	 */
	public function goAction()
	{
		$this->_initCheckout();
		$this->getResponse()->setBody($this->getLayout()->createBlock('sagepaysuite/checkout_formpost')->toHtml());
		return;
	}

	public function successAction()
	{
		$_r = $this->getRequest();

		Sage_Log::log($_r->getPost(), null, 'SagePaySuite_FORM_Callback.log');

		if($_r->getParam('crypt') && $_r->getParam('vtxc')){

			$strDecoded = $this->getFormModel()->decrypt($_r->getParam('crypt'));
			$token = Mage::helper('sagepaysuite/form')->getToken($strDecoded);

			Ebizmarts_SagePaySuite_Log::w($token, null, 'SagePaySuite_FORM_Callback.log');

			$db = Mage::helper('sagepaysuite')->arrayKeysToUnderscore($token);

			# Add data to DB transaction
			$trn = $this->_getTransaction()->loadByVendorTxCode($_r->getParam('vtxc'));

			$trn->addData($db);

			if(isset($db['post_code_result'])){
				$trn->setPostcodeResult($db['post_code_result']);
			}
			if(isset($db['cv2_result'])){
				$trn->setCv2result($db['cv2_result']);
			}
			if(isset($db['3_d_secure_status'])){
				$trn->setThreedSecureStatus($db['3_d_secure_status']);
			}
			if(isset($db['last4_digits'])){
				$trn->setLastFourDigits($db['last4_digits']);
			}
			if(isset($db['gift_aid'])){
				$trn->setGiftAid($db['gift_aid']);
			}

			$trn->save();

			Mage::register('sageserverpost', new Varien_Object($token));

			if(strtoupper($trn->getTxType()) == 'PAYMENT'){
				Mage::getSingleton('sagepaysuite/session')->setInvoicePayment(true);
			}

 			$this->getOnepage()->getQuote()->collectTotals();
        	$this->getOnepage()->saveOrder();

			Mage::helper('sagepaysuite/checkout')->deleteQuote();

			$this->_redirect('checkout/onepage/success');
			return;

		}

		$this->_redirect('/');
		return;
	}
	public function failureAction()
	{
		$_r = $this->getRequest();

		if($_r->getParam('crypt') && $_r->getParam('vtxc')){

			# Delete orphan transaction from DB
			$trn = $this->_getTransaction()->loadByVendorTxCode($_r->getParam('vtxc'));
            if($trn->getId()){
            	$trn->delete();
            }

            $this->_getSession()->addError($this->__('Transaction has been canceled.'));
			$this->_redirect('checkout/cart');
			return;

		}

		$this->_redirect('/');
		return;
	}
}

