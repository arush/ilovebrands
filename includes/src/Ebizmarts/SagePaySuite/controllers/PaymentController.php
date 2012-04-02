<?php

/**
 * SUITE payment controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_PaymentController extends Mage_Core_Controller_Front_Action
{

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function getSageSuiteSession() {
        return Mage::getSingleton('sagepaysuite/session');
    }

    protected function _getQuote()
    {
        return Mage::getSingleton('sagepaysuite/api_payment')->getQuote();
    }

	/**
	 * Return all customer cards list for onepagecheckout use.
	 */
	public function getTokenCardsHtmlAction() {
		$html = '';

		$_code = $this->getRequest()->getPost('payment_method', 'sagepaydirectpro');

		try {

			$html .= $this->getLayout()->createBlock('sagepaysuite/form_tokenList', 'token.cards.li')
						  ->setCanUseToken(true)
						  ->setPaymentMethodCode($_code)
						  ->toHtml();

		} catch (Exception $e) {
			Ebizmarts_SagePaySuite_Log :: we($e);
		}

		return $this->getResponse()->setBody(str_replace(array (
			'<div id="tokencards-payment-' . $_code . '">',
			'</div>'
		), array (), $html));
	}

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

	private function _OSCSaveBilling()
	{
        $helper = Mage::helper('onestepcheckout/checkout');

        $billing_data = $this->getRequest()->getPost('billing', array());
        $shipping_data = $this->getRequest()->getPost('shipping', array());
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);

        $billing_data = $helper->load_exclude_data($billing_data);
        $shipping_data = $helper->load_exclude_data($shipping_data);

		if( !Mage::helper('customer')->isLoggedIn() ){

			  $registration_mode = Mage::getStoreConfig('onestepcheckout/registration/registration_mode');
			  if($registration_mode == 'auto_generate_account')   {
				  // Modify billing data to contain password also
				  $password = Mage::helper('onestepcheckout/checkout')->generatePassword();
				  $billing_data['customer_password'] = $password;
				  $billing_data['confirm_password'] = $password;
				  $this->getOnepage()->getQuote()->getCustomer()->setData('password', $password);
				  $this->getOnepage()->getQuote()->setData('password_hash', Mage::getModel('customer/customer')->encryptPassword($password));

					$this->getOnepage()->getQuote()->setData('customer_email', $billing_data['email']);
					$this->getOnepage()->getQuote()->setData('customer_firstname', $billing_data['firstname']);
					$this->getOnepage()->getQuote()->setData('customer_lastname', $billing_data['lastname']);

			  }


			  if($registration_mode == 'require_registration' || $registration_mode == 'allow_guest')   {
				  if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']) && ($billing_data['customer_password'] == $billing_data['confirm_password'])){
					  $password = $billing_data['customer_password'];
					  $this->getOnepage()->getQuote()->setCheckoutMethod('register');
					  $this->getOnepage()->getQuote()->getCustomer()->setData('password', $password);

					  $this->getOnepage()->getQuote()->setData('customer_email', $billing_data['email']);
					  $this->getOnepage()->getQuote()->setData('customer_firstname', $billing_data['firstname']);
					  $this->getOnepage()->getQuote()->setData('customer_lastname', $billing_data['lastname']);

					  $this->getOnepage()->getQuote()->setData('password_hash', Mage::getModel('customer/customer')->encryptPassword($password));
				  }
			  }

			if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']))   {
				// Trick to allow saving of
				Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod('register');
			}

		}//Create Account hook


        if(Mage::helper('customer')->isLoggedIn() && $helper->differentShippingAvailable()){
            if(!empty($customerAddressId)){
                $billingAddress = Mage::getModel('customer/address')->load($customerAddressId);
                if(is_object($billingAddress) && $billingAddress->getCustomerId() ==  Mage::helper('customer')->getCustomer()->getId()){
                    $billing_data = array_merge($billing_data, $billingAddress->getData());
                }
            }
            if(!empty($shippingAddressId)){
                $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
                if(is_object($shippingAddress) && $shippingAddress->getCustomerId() ==  Mage::helper('customer')->getCustomer()->getId()){
                    $shipping_data = array_merge($shipping_data, $shippingAddress->getData());
                }
            }
        }

        if(!empty($billing_data['use_for_shipping'])) {
           $shipping_data = $billing_data;
        }

        $this->getOnepage()->getQuote()->getBillingAddress()->addData($billing_data)->implodeStreetAddress()->setCollectShippingRates(true);

        $paymentMethod = $this->getRequest()->getPost('payment_method', false);
        $selectedMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();

        $store = $this->getOnepage()->getQuote() ? $this->getOnepage()->getQuote()->getStoreId() : null;
        $methods = $helper->getActiveStoreMethods($store, $this->getOnepage()->getQuote());

        if($paymentMethod && !empty($methods) && !in_array($paymentMethod, $methods)){
            $paymentMethod = false;
        }

        if(!$paymentMethod && $selectedMethod && in_array($selectedMethod, $methods)){
             $paymentMethod = $selectedMethod;
        }

        if($this->getOnepage()->getQuote()->isVirtual()) {
            $this->getOnepage()->getQuote()->getBillingAddress()->setPaymentMethod(!empty($paymentMethod) ? $paymentMethod : null);
        } else {
            $this->getOnepage()->getQuote()->getShippingAddress()->setPaymentMethod(!empty($paymentMethod) ? $paymentMethod : null);
        }

        try {
            if($paymentMethod){
                $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();
            }
        } catch (Exception $e) {
        }

        $result = $this->getOnepage()->saveBilling($billing_data, $customerAddressId);

        if($helper->differentShippingAvailable()) {
            if(empty($billing_data['use_for_shipping'])) {
                $shipping_result = $helper->saveShipping($shipping_data, $shippingAddressId);
            }else {
                $shipping_result = $helper->saveShipping($billing_data, $customerAddressId);
            }
        }

        $shipping_method = $this->getRequest()->getPost('shipping_method', false);

        if(!empty($shipping_method)) {
           $helper->saveShippingMethod($shipping_method);
        }

        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();

		$requestParams = $this->getRequest()->getParams();
		if(array_key_exists('onestepcheckout_comments', $requestParams)
		  && !empty($requestParams['onestepcheckout_comments'])){
			$this->getSageSuiteSession()->setOscOrderComments($requestParams['onestepcheckout_comments']);
		}

		if(array_key_exists('subscribe_newsletter', $requestParams)
		  && (int)$requestParams['subscribe_newsletter'] === 1){
			$this->getSageSuiteSession()->setOscNewsletterEmail($this->getOnepage()->getQuote()->getBillingAddress()->getEmail());
		}

	}

    /**
     * Create order action
     */
    public function onepageSaveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

		$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();

		if((FALSE === strstr(parse_url($this->_getRefererUrl(), PHP_URL_PATH), 'onestepcheckout')) && is_null($this->getRequest()->getPost('billing'))){ // Not OSC, OSC validates T&C with JS and has it own T&C
	        # Validate checkout Terms and Conditions
	        $result = array();
	        if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
	            $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
	            if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
	                $result['success'] = false;
	                $result['response_status'] = 'ERROR';
	                $result['response_status_detail'] = $this->__('Please agree to all the terms and conditions before placing the order.');
	                $this->getResponse()->setBody(Zend_Json::encode($result));
	                return;
	            }
	        }
	        # Validate checkout Terms and Conditions
		}else{

		/**
		 * OSC
		 */
			if(FALSE !== Mage::getConfig()->getNode('modules/Idev_OneStepCheckout')){
				$this->_OSCSaveBilling();
			}
		/**
		 * OSC
		 */
		}

        if ($data = $this->getRequest()->getPost('payment', false)) {
            $this->getOnepage()->getQuote()->getPayment()->importData($data);
        }

		if($dataM = $this->getRequest()->getPost('shipping_method', '')){
			$this->getOnepage()->saveShippingMethod($dataM);
		}

		if($paymentMethod == 'sagepaypaypal'){
			$resultData = array (
									'success' => 'true',
									'response_status' => 'paypal_redirect',
									'redirect' => Mage::getModel('core/url')->addSessionParam()->getUrl('sgps/paypalexpress/go', array('_secure' => true))
								);

			return $this->getResponse()->setBody(Zend_Json :: encode($resultData));
		}

        if($paymentMethod == 'sagepayserver'){
            $this->_forward('saveOrder', 'serverPayment', null, $this->getRequest()->getParams());
            return;
        }else if($paymentMethod == 'sagepaydirectpro'){
            $this->_forward('saveOrder', 'directPayment', null, $this->getRequest()->getParams());
            return;
        }else if($paymentMethod == 'sagepayform'){
            $this->_forward('saveOrder', 'formPayment', null, $this->getRequest()->getParams());
            return;
        }else{
            $this->_forward('saveOrder', 'onepage', 'checkout', $this->getRequest()->getParams());
            return;
        }

    }

}
