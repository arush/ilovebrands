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
            $this->_forward('saveOrder', 'onepage', 'Mage_Checkout');
            return;
        }
    }

}