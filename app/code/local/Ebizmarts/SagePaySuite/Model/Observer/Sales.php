<?php

/**
 * Sales event observer
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_Observer_Sales extends Ebizmarts_SagePaySuite_Model_Observer
{

    public function loadAfter($o)
    {
        $order = $o->getEvent()->getOrder();

        $trn = $this->_getTransactionsModel()
                ->loadByParent($order->getId());

        $order->setData('sagepay_info', $trn);
    }

	public function saveAfter($o)
	{
		$order = $o->getEvent()->getOrder();

		$isSage = Mage::helper('sagepaysuite')->isSagePayMethod($order->getPayment()->getMethod());

		if($isSage === false){
			return $o;
		}

		if($this->_getTransactionsModel()->loadByParent($order->getId())->getId()){
			return $o;
		}

		if((int)Mage::getStoreConfig('payment/sagepaysuite/order_error_save', Mage::app()->getStore()->getId()) === 1){
			Mage::throwException(Mage::getStoreConfig('payment/sagepaysuite/order_error_save_message', Mage::app()->getStore()->getId()));
		}

		$rqVendorTxCode = Mage::app()->getRequest()->getParam('vtxc');
        $sessionVendor = ($rqVendorTxCode) ? $rqVendorTxCode : $this->getSession()->getLastVendorTxCode();

		/**
		 * Multishipping vendors
		 */
		$multiShippingTxCodes = Mage::registry('sagepaysuite_ms_txcodes');
		if($multiShippingTxCodes){

			Mage::unregister('sagepaysuite_ms_txcodes');

			$sessionVendor = current($multiShippingTxCodes);

			array_shift($multiShippingTxCodes);
			reset($multiShippingTxCodes);

			Mage::register('sagepaysuite_ms_txcodes', $multiShippingTxCodes);

		}
		/**
		 * Multishipping vendors
		 */

        $reg = Mage::registry('Ebizmarts_SagePaySuite_Model_Api_Payment::recoverTransaction');
        if(!is_null($reg)){
        	$sessionVendor = $reg;
        }

        if(is_null($sessionVendor)){

			$dbtrn = $this->_getTransactionsModel()->loadByParent($order->getId());
        	if(!$dbtrn->getId()){

				#For empty payments or old orders (standalone payment methods).
				if( (Mage::app()->getRequest()->getControllerModule() == 'Mage_Api') || Mage::registry('current_shipment') || Mage::registry('sales_order') || Mage::registry('current_creditmemo') || Mage::registry('current_invoice')){
					return $o;
				}

        		$logfileName = $order->getIncrementId() . '-' . time() . '_Payment_Failed.log';

				$request_data = $_REQUEST;
				if( isset($request_data['payment']) ){
					$request_data['payment']['cc_number'] = 'XXXXXXXXXXXXX';
					$request_data['payment']['cc_cid'] = 'XXX';
				}

				Sage_Log::log($order->getIncrementId(), null, $logfileName);
				Sage_Log::log(Mage::helper('core/http')->getHttpUserAgent(false), null, $logfileName);
				Sage_Log::log(print_r($request_data, true), null, $logfileName);
				Sage_Log::log('--------------------', null, $logfileName);

				Mage::throwException('Payment has failed, please reload checkout page and try again. Your card has not been charged.');
        	}

            return $o;
        }

		$this->_handleOscCallbacks();

		$tran = $this->_getTransactionsModel()
            ->loadByVendorTxCode($sessionVendor)
            ->setOrderId($order->getId());
		if($tran->getId()){

			if($tran->getToken()){
				$token = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->loadByToken($tran->getToken());
				if($token->getId()){
					$tran->setCardType($token->getCardType())
						->setLastFourDigits($token->getLastFour());
				}
			}

			$tran->save();

		}

        # Invoice automatically PAYMENT transactions
        if($this->getSession()->getInvoicePayment() || (!is_null($reg) && $tran->getTxType() == 'PAYMENT')){
            $this->getSession()->unsetData('invoice_payment');
            Mage::getModel('sagepaysuite/api_payment')->invoiceOrder($order->getId());
        }
	}

    public function saveBefore($o)
    {
        $order = $o->getEvent()->getOrder();

        $payment = $order->getPayment();

		/**
		 * Add OSC comments to ORDER
		 */
		if($this->getSession()->getOscOrderComments()){
			$order->setOnestepcheckoutCustomercomment($this->getSession()->getOscOrderComments());
		}

        if ($payment->getMethod() != 'sagepaydirectpro' && $payment->getMethod() != 'sagepayserver') {
            return $o;
        }

        if ($payment->getStatus() == 'FAIL' && $payment->getMethod() == 'sagepaydirectpro') {
            $order->setStatus(Mage::getStoreConfig('payment/sagepaysuite/fail_order_status'));
        }

        if ($payment->getMethod() != 'sagepaydirectpro') {
            return $o;
        }

        if($payment->getStatus() == 'FAIL') {
            $order->setStatus(Mage::getStoreConfig('payment/sagepaydirectpro/fail_order_status'));
        }

    }

	public function updateButton($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Button') {
            if($block->getRequest()->getControllerName() == 'sales_order_create' && $block->getData('onclick') == 'order.submit()') {
                $block->setData('onclick', 'SageSuiteCreateOrder.orderSave();');
            }
        }
    }

	public function quoteToOrder(Varien_Event_Observer $observer)
	{
		$sessionOrderId = $this->getSession()->getReservedOrderId();

		if(!$sessionOrderId){
			return $observer;
		}

		$order = $observer->getEvent()->getOrder();
		$quote = $observer->getEvent()->getQuote();

		$order->setIncrementId($sessionOrderId);
		$quote->setIncrementId($sessionOrderId);
	}

	/**
	 * Handle OneStepCheckout callbacks
	 */
	protected function _handleOscCallbacks()
	{
		$newsletterEmail = $this->getSession()->getOscNewsletterEmail();
		if($newsletterEmail){
			$this->_oscSuscribeNewsletter($newsletterEmail);
		}
	}

	protected function _oscSuscribeNewsletter($customerEmail)
	{
		try{
	        $model = Mage::getModel('newsletter/subscriber');
	        $result = $model->loadByEmail($customerEmail);

	        if($result->getId() === NULL)   {
	            // Not subscribed, OK to subscribe
	            Mage::getModel('newsletter/subscriber')->subscribe($customerEmail);
	        }
		}catch(Exception $e){
			Sage_Log::logException($e);
		}
	}

}