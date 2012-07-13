<?php

/**
 * API model access for SagePay
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Model_Api_Payment extends Mage_Payment_Model_Method_Cc {

    protected $_code = '';
    protected $_canManageRecurringProfiles  = false;
	protected $_quote = null;

	protected $_canEdit = false;

    const BASKET_SEP = ':';
    const RESPONSE_DELIM_CHAR = "\r\n";

    const REQUEST_BASKET_ITEM_DELIMITER = ':';
    const RESPONSE_CODE_APPROVED = 'OK';
    const RESPONSE_CODE_REGISTERED = 'REGISTERED';
    const RESPONSE_CODE_DECLINED = 'OK';
    const RESPONSE_CODE_ABORTED = 'OK';
    const RESPONSE_CODE_AUTHENTICATED = 'OK';
    const RESPONSE_CODE_REJECTED = 'REJECTED';
    const RESPONSE_CODE_INVALID = 'INVALID';
    const RESPONSE_CODE_ERROR = 'ERROR';
    const RESPONSE_CODE_NOTAUTHED = 'NOTAUTHED';
    const RESPONSE_CODE_3DAUTH = '3DAUTH';
    const RESPONSE_CODE_MALFORMED = 'MALFORMED';

    const REQUEST_TYPE_AUTHENTICATE = 'AUTHENTICATE';
    const REQUEST_TYPE_DEFERRED = 'DEFERRED';
    const REQUEST_TYPE_PAYMENT = 'PAYMENT';
    const REQUEST_TYPE_RELEASE = 'RELEASE';
    const REQUEST_TYPE_ABORT = 'ABORT';
    const REQUEST_TYPE_REFUND = 'REFUND';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_CANCEL = 'CANCEL';
    const REQUEST_TYPE_AUTHORISE = 'AUTHORISE';

    const XML_CREATE_INVOICE = 'payment/sagepaydirectpro/create_invoice';

    const REQUEST_METHOD_CC = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';

    const REQUEST_TYPE_REPEAT = 'REPEAT';
    const REQUEST_TYPE_REPEATDEFERRED = 'REPEATDEFERRED';
    const STATUS_REGISTERED = 'REGISTERED';
    const RESPONSE_CODE_APPROVED_DEFERRED = 'OK_DEFERRED';
    const RESPONSE_CODE_APPROVED_RELEASED = 'OK_RELEASED';
    const RESPONSE_CODE_APPROVED_AUTHENTICATED = 'OK_AUTHENTICATED';

	const CARD_TYPE_PAYPAL = 'PAYPAL';
	const RESPONSE_CODE_PAYPAL_REDIRECT = 'PPREDIRECT';
	const RESPONSE_CODE_PAYPAL_OK = 'PAYPALOK';

    const ACTION_AUTHORIZE_CAPTURE = 'payment';
    const ACTION_AUTHORIZE         = 'deferred';
    const ACTION_AUTHENTICATE      = 'authenticate';

    protected $ACSURL = NULL;
    protected $PAReq = NULL;
    protected $MD = NULL;
    private $_sharedConf = array('email_on_invoice', 'trncurrency', 'referrer_id', 'vendor', 'timeout_message', 'connection_timeout', 'send_basket');

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->_canEdit;
    }

	protected function _getCoreUrl()
	{
		return Mage::getModel('core/url');
	}

	protected function _getServerUrlParams()
	{
		$params = array();

		if($this->_isMultishippingCheckout() === true){
			$params ['multishipping'] = 1;
		}

		$params ['storeid'] = Mage::app()->getStore()->getId();
		$params ['qid'] = (int)Mage::app()->getRequest()->getParam('qid');

		return $params;
	}

	public function getNotificationUrl() {
		if ($this->_getIsAdmin()) {
			return Mage :: getModel('adminhtml/url')->getUrl('sgpsSecure/adminhtml_serverPayment/notifyAdminOrder', array (
				'_secure' => true,
				'form_key' => Mage::getSingleton('core/session')->getFormKey(),
				'_nosid' => true
			)) . '?' . $this->getSidParam();
		} else {
			$params = array('_secure' => true);

			return $this->_getCoreUrl()->addSessionParam()->getUrl('sgps/ServerPayment/notify', array_merge($params, $this->_getServerUrlParams()));
		}
	}

	public function getSuccessUrl()
	{
		if ($this->_getIsAdmin()) {
			return Mage :: getModel('adminhtml/url')->getUrl('sgpsSecure/adminhtml_serverPayment/success', array (
				'_secure' => true,
				#'form_key' => Mage::getSingleton('core/session')->getFormKey(),
				'_nosid' => true
			)) . '?' . $this->getSidParam();
		} else {
			$params = array('_secure' => true);
			return $this->_getCoreUrl()->addSessionParam()->getUrl('sgps/ServerPayment/success', array_merge($params, $this->_getServerUrlParams()));
		}
	}

	public function getRedirectUrl()
	{
		if ($this->_getIsAdmin()) {
			return Mage :: getModel('adminhtml/url')->getUrl('sgpsSecure/adminhtml_serverPayment/redirect', array (
				'_secure' => true,
				#'form_key' => Mage::getSingleton('core/session')->getFormKey(),
				'_nosid' => true
			)) . '?' . $this->getSidParam();
		} else {
			$params = array('_secure' => true);
			return $this->_getCoreUrl()->addSessionParam()->getUrl('sgps/payment/redirect', array_merge($params, $this->_getServerUrlParams()));
		}
	}

	public function getFailureUrl()
	{
		if ($this->_getIsAdmin()) {
			return Mage :: getModel('adminhtml/url')->getUrl('sgpsSecure/adminhtml_serverPayment/failure', array (
				'_secure' => true,
				#'form_key' => Mage::getSingleton('core/session')->getFormKey(),
				'_nosid' => true
			)) . '?' . $this->getSidParam();
		} else {
			$params = array('_secure' => true);
			return $this->_getCoreUrl()->addSessionParam()->getUrl('sgps/ServerPayment/failure', array_merge($params, $this->_getServerUrlParams()));
		}
	}

    public function getTransactionDetails($orderId)
    {
        return Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->loadByParent($orderId);
    }

	public function getNewTxCode()
	{
		return substr(time(),0,39);
	}

    public function getDate($format = 'Y-m-d H:i:s') {
        return Mage::getSingleton('core/date')->date($format);
    }

    public function getVpsProtocolVersion() {
        return 2.23;
    }

    public function getCustomerQuoteId() {
        $id = null;

        if (Mage::getSingleton('adminhtml/session_quote')->getQuoteId()) { #Admin
            $id = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        } else if(Mage::getSingleton('customer/session')->getCustomerId()){ #Logged in frontend
            $id = Mage::getSingleton('customer/session')->getCustomerId();
        }else{ #Guest/Register
            $vdata = Mage::getSingleton('core/session')->getVisitorData();
            return (string) $vdata['session_id'];
        }

        return (int) $id;
    }

	public function getCustomerLoggedEmail()
	{
		$s = Mage::getSingleton('customer/session');
		if($s->getCustomerId()){
			return $s->getCustomer()->getEmail();
		}
		return null;
	}

	public function setMcode($code)
	{
		$this->_code = $code;
		return $this;
	}

    /**
     * Retrieve information from payment configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null) {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        $_code = (!in_array($field, $this->_sharedConf) ? $this->getCode() : 'sagepaysuite');

        if( $_code != 'sagepaysuite' && strpos($this->getCode(), 'moto') !== FALSE ){
        	$_code = $this->getCode();
        	if( Mage::getSingleton('adminhtml/session_quote')->getStoreId() ){
        		$storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        	}
        }

        $path = 'payment/' . $_code . '/' . $field;

        $value = Mage::getStoreConfig($path, $storeId);

        if ($field == 'timeout_message') {
            $store = Mage::app()->getStore($storeId);
            $value = $this->_sageHelper()->__(str_replace(array('{store_name}', '{admin_email}'), array($store->getName(), Mage::getStoreConfig('trans_email/ident_general/email', $storeId)), Mage::getStoreConfig('payment/sagepaysuite/timeout_message', $storeId)));
        }

        return $value;
    }

    public function getUrl($key, $tdcall = false, $code = null, $mode = null) {
        if ($tdcall) {
            $key = $key.='3d';
        }

        $_code = (is_null($code) ? $this->getCode() : $code);
        $_mode = (is_null($mode) ? $this->getConfigData('mode') : $mode);

		$urls = Mage::helper('sagepaysuite')->getSagePayUrlsAsArray();

        return $urls[$_code][$_mode][$key];
    }

    public function getTokenUrl($key, $integration) {
        $confKey = ($integration == 'direct') ? 'sagepaydirectpro' : 'sagepayserver';
        $urls = Mage::helper('sagepaysuite')->getSagePayUrlsAsArray();
        return $urls['sagepaytoken'][Mage::getStoreConfig('payment/' . $confKey . '/mode', Mage::app()->getStore()->getId())][$integration . $key];
    }

    public function getSidParam() {
        $coreSession = Mage::getSingleton('core/session');
        $sessionIdQueryString = $coreSession->getSessionIdQueryParam() . '=' . $coreSession->getSessionId();

        return $sessionIdQueryString;
    }

    public function getTokenModel() {
        return Mage::getModel('sagepaysuite/sagePayToken');
    }

    public static function log($data, $level = null, $file = null) {
        Sage_Log::log($data, $level, $file);
    }

    protected function _tokenPresent() {
        try {
            $present = (bool) ((int) $this->getInfoInstance()->getSagepayTokenCcId() !== 0);
        } catch (Exception $e) {
            if ((int) $this->getSageSuiteSession()->getLastSavedTokenccid() !== 0) {
                $present = true;
            } else {
                $present = false;
            }
        }

        return $present;
    }

    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        if (!$data->getSagepayTokenCcId() && $this->getSageSuiteSession()->getLastSavedTokenccid()) {
            $data->setSagepayTokenCcId($this->getSageSuiteSession()->getLastSavedTokenccid());
        }else if($data->getSagepayTokenCcId()){
        	$this->getSageSuiteSession()->setLastSavedTokenccid($data->getSagepayTokenCcId());
        }

		$this->getSageSuiteSession()->setTokenCvv($data->getTokenCvv());

		if($this->isMobile()){
			$cct = Mage::getSingleton('sagepaysuite/config')->getTranslateCc();
			if(in_array($data->getCcType(), $cct)){
				$cctF = array_flip($cct);
				$data->setCcType($cctF[$data->getCcType()]);
			}
		}

		//Direct GiftAidPayment flag
		$dgift = (!is_null($data->getCcGiftaid()) ? 1 : NULL);

		//Remember token
		$info->setRemembertoken((!is_null($data->getRemembertoken()) ? 1 : 0));

        $info->setCcType($data->getCcType())
                ->setCcOwner($data->getCcOwner())
                ->setCcLast4(substr($data->getCcNumber(), -4))
                ->setCcNumber($data->getCcNumber())
                ->setCcCid($data->getCcCid())
                ->setSagepayTokenCcId($data->getSagepayTokenCcId())
                ->setCcExpMonth($data->getCcExpMonth())
                ->setCcExpYear($data->getCcExpYear())
                ->setCcIssue($data->getCcIssue())
                ->setSaveTokenCc($data->getSavecc())
                ->setTokenCvv($data->getTokenCvv())
                ->setCcStartMonth($data->getCcStartMonth())
                ->setCcStartYear($data->getCcStartYear())
                ->setCcGiftaid($dgift);
        return $this;
    }

    protected function _getQuote() {

		$opQuote = Mage::getSingleton('checkout/type_onepage')->getQuote();
		$adminQuote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

		$rqQuoteId = Mage::app()->getRequest()->getParam('qid');
        if($adminQuote->hasItems() === false && (int)$rqQuoteId){
        	$opQuote->setQuote(Mage::getModel('sales/quote')->loadActive($rqQuoteId));
        }

        return ($adminQuote->hasItems() === true) ? $adminQuote : $opQuote;

    }

    public function getQuote() {
        return $this->_getQuote();
    }

    /**
     * Check if current quote is multishipping
     */
    protected function _isMultishippingCheckout() {
        return (bool) Mage::getSingleton('checkout/session')->getQuote()->getIsMultiShipping();
    }

	public function cleanInput($strRawText, $strType)
	{
		if ($strType == "Number") {
			$strClean = "0123456789.";
			$bolHighOrder = false;
		} else
			if ($strType == "VendorTxCode") {
				$strClean = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
				$bolHighOrder = false;
			} else {
				$strClean = " ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,'/{}@():?-_&�$=%~<>*+\"";
				$bolHighOrder = true;
			}

		$strCleanedText = "";
		$iCharPos = 0;

		do {
			// Only include valid characters
			$chrThisChar = substr($strRawText, $iCharPos, 1);

			if (strspn($chrThisChar, $strClean, 0, strlen($strClean)) > 0) {
				$strCleanedText = $strCleanedText . $chrThisChar;
			} else
				if ($bolHighOrder == true) {
					// Fix to allow accented characters and most high order bit chars which are harmless
					if (bin2hex($chrThisChar) >= 191) {
						$strCleanedText = $strCleanedText . $chrThisChar;
					}
				}

			$iCharPos = $iCharPos +1;
		} while ($iCharPos < strlen($strRawText));

		$cleanInput = ltrim($strCleanedText);
		return $cleanInput;

	}

    protected function _cleanString($text) {
        $pattern = '|[^a-zA-Z0-9\-\._]+|';
        $text = preg_replace($pattern, '', $text);

        return $text;
    }

	protected function _cphone($phone)
	{
		return preg_replace('/[^a-zA-Z0-9\s]/', '', $phone);
	}

    public function cleanString($text) {
        return $this->_cleanString($text);
    }

	protected function _getAdminSession()
	{
		return Mage::getSingleton('adminhtml/session');
	}

    /**
     * Check if admin is logged in
     * @return bool
     */
    protected function _getIsAdmin() {
        return (bool) (Mage::getSingleton('admin/session')->isLoggedIn());
    }

    /**
     * Check if current transaction is from the Backend
     * @return bool
     */
    protected function _getIsAdminOrder() {
        return (bool) (Mage::getSingleton('admin/session')->isLoggedIn() &&
        Mage::getSingleton('adminhtml/session_quote')->getQuoteId());
    }

    /**
     * Return commno data for *all* transactions.
     * @return array Data
     */
    public function _getGeneralTrnData(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        $quoteObj = $this->_getQuote();

        $vendorTxCode = $this->_getTrnVendorTxCode();

        if ($payment->getCcNumber()) {
            $vendorTxCode .= $this->_cleanString(substr($payment->getCcOwner(), 0, 10));
        }
        $payment->setVendorTxCode($vendorTxCode);

        $request = new Varien_Object;
        $request->setVPSProtocol('2.23')
                ->setReferrerID($this->getConfigData('referrer_id'))
                ->setVendor($this->getConfigData('vendor'))
                ->setVendorTxCode($vendorTxCode);

		$request->setClientIPAddress($this->getClientIp());

		if($payment->getIntegra()){

			$this->getSageSuiteSession()->setLastVendorTxCode($vendorTxCode);
			$request->setIntegration($payment->getIntegra());
			$request->setData('notification_URL', $this->getNotificationUrl() . '&vtxc='.$vendorTxCode);
			$request->setData('success_URL', $this->getSuccessUrl());
			$request->setData('redirect_URL', $this->getRedirectUrl());
			$request->setData('failure_URL', $this->getFailureUrl());

		}

        if ($this->_getIsAdminOrder()) {
            $request->setAccountType('M');
        }

        if ($payment->getAmountOrdered()) {
            $from = $order->getOrderCurrencyCode();

			if((string)$this->getConfigData('trncurrency') == 'store'){
	        	$request->setAmount( $this->formatAmount($quoteObj->getGrandTotal(), $quoteObj->getQuoteCurrencyCode()) );
	            $request->setCurrency($quoteObj->getQuoteCurrencyCode());
	        }else{
	            $request->setAmount( $this->formatAmount($quoteObj->getBaseGrandTotal(), $quoteObj->getBaseCurrencyCode()) );
	            $request->setCurrency($quoteObj->getBaseCurrencyCode());
	        }
        }

        if (!empty($order)) {

            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setBillingAddress($billing->getStreet(1) . ' ' . $billing->getCity() . ' ' .
                                $billing->getRegion() . ' ' . $billing->getCountry()
                        )
                        ->setBillingSurname($this->ss($billing->getLastname(), 20))
                        ->setBillingFirstnames($this->ss($billing->getFirstname(), 20))
                        ->setBillingPostCode($this->ss($billing->getPostcode(), 10))
                        ->setBillingAddress1($this->ss($billing->getStreet(1), 100))
                        ->setBillingAddress2($this->ss($billing->getStreet(2), 100))
                        ->setBillingCity($this->ss($billing->getCity(), 40))
                        ->setBillingCountry($billing->getCountry())
                        ->setContactNumber(substr($this->_cphone($billing->getTelephone()),0,20));

                if ($billing->getCountry() == 'US') {
                    $request->setBillingState($billing->getRegionCode());
                }

				$request->setCustomerEMail($billing->getEmail());
            }

			if(!$request->getDescription()){
				$request->setDescription('.');
			}

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setDeliveryAddress($shipping->getStreet(1) . ' ' . $shipping->getCity() . ' ' .
                                $shipping->getRegion() . ' ' . $shipping->getCountry()
                        )
                        ->setDeliverySurname($this->ss($shipping->getLastname(), 20))
                        ->setDeliveryFirstnames($this->ss($shipping->getFirstname(), 20))
                        ->setDeliveryPostCode($this->ss($shipping->getPostcode(), 10))
                        ->setDeliveryAddress1($this->ss($shipping->getStreet(1), 100))
                        ->setDeliveryAddress2($this->ss($shipping->getStreet(2), 100))
                        ->setDeliveryCity($this->ss($shipping->getCity(), 40))
                        ->setDeliveryCountry($shipping->getCountry())
                        ->setDeliveryPhone($this->ss(urlencode($this->_cphone($shipping->getTelephone())), 20));

                if ($shipping->getCountry() == 'US') {
                    $request->setDeliveryState($shipping->getRegionCode());
                }
            } else {
                #If the cart only has virtual products, I need to put an shipping address to Sage Pay.
                #Then the billing address will be the shipping address to
                $request->setDeliveryAddress($billing->getStreet(1) . ' ' . $billing->getCity() . ' ' .
                                $billing->getRegion() . ' ' . $billing->getCountry()
                        )
                        ->setDeliverySurname($this->ss($billing->getLastname(), 20))
                        ->setDeliveryFirstnames($this->ss($billing->getFirstname(), 20))
                        ->setDeliveryPostCode($this->ss($billing->getPostcode(), 10))
                        ->setDeliveryAddress1($this->ss($billing->getStreet(1), 100))
                        ->setDeliveryAddress2($this->ss($billing->getStreet(2), 100))
                        ->setDeliveryCity($this->ss($billing->getCity(), 40))
                        ->setDeliveryCountry($billing->getCountry())
                        ->setDeliveryPhone($this->ss(urlencode($this->_cphone($billing->getTelephone())), 20));

                if ($billing->getCountry() == 'US') {
                    $request->setDeliveryState($billing->getRegionCode());
                }
            }
        }
        if ($payment->getCcNumber()) {
            $request->setCardNumber($payment->getCcNumber())
                    ->setExpiryDate(sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), strlen($payment->getCcExpYear()) - 2)))
                    ->setCardType($payment->getCcType())
                    ->setCV2($payment->getCcCid())
                    ->setCardHolder($payment->getCcOwner());

            if ($payment->getCcIssue()) {
                $request->setIssueNumber($payment->getCcIssue());
            }
            if ($payment->getCcStartMonth() && $payment->getCcStartYear()) {
                $request->setStartDate(sprintf('%02d%02d', $payment->getCcStartMonth(), substr($payment->getCcStartYear(), strlen($payment->getCcStartYear()) - 2)));
            }
        }

        $totals = $shipping->getTotals();
        $shippingTotal = isset($totals['shipping']) ? $totals['shipping']->getValue() : 0;
		if($this->getSendBasket()){
			$request->setBasket($this->_getBasketContents($quoteObj));
		}

        if(!$request->getDeliveryPostCode()){
        	$request->setDeliveryPostCode('000');
        }
        if(!$request->getBillingPostCode()){
        	$request->setBillingPostCode('000');
        }

        return $request;
    }

    /**
     * Invoice existing order
     *
     * @param int $id Order id
     * @param string $captureMode Mode capture, OFFLINE-ONLINE-NOTCAPTURE
     */
    public function invoiceOrder($id = null, $captureMode = Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE, $silent = true){

        if( is_object($id) ){
        	$order = $id;
        }else{
        	$order = Mage::getModel('sales/order')->load($id);
        }

        try {
            if (!$order->canInvoice()) {
            	$emessage = $this->_getCoreHelper()->__('Cannot create an invoice.');
            	if(!$silent){
            		Mage::throwException($emessage);
            	}
                Sage_Log::log($emessage);
                return false;
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
            	$emessage = $this->_getCoreHelper()->__('Cannot create an invoice without products.');
            	if(!$silent){
            		Mage::throwException($emessage);
            	}
                Sage_Log::log($emessage);
                return false;
            }

            $invoice->setRequestedCaptureCase($captureMode);

            # New in 1.4.2.0, if there is not such value, only REFUND OFFLINE shows up
            # TODO: @see Mage_Sales_Model_Order_Payment::registerCaptureNotification
            //$invoice->setTransactionId($order->getSagepayInfo()->getId());
            $invoice->setTransactionId(time());

            $invoice->register();

			//Send email
			$sendemail = (bool)$this->getConfigData('email_on_invoice');
            $invoice->setEmailSent($sendemail);
            $invoice->getOrder()->setCustomerNoteNotify($sendemail);

            $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());

            $transactionSave->save();

			if($sendemail){
	            try {
	                $invoice->sendEmail(TRUE, '');
	            } catch (Exception $em) {
	                Mage::logException($em);
	            }
			}

            return true;
        } catch (Mage_Core_Exception $e) {
        	if(!$silent){
        		Mage::throwException($e->getMessage());
        	}
            Sage_Log::logException($e);
            return false;
        }
    }

	public function getClientIp()
	{
		return Mage::helper('core/http')->getRemoteAddr();
	}


    /**
     * Get product customize options
     *
     * @return array || false
     */
    protected function _getProductOptions($item)
    {
    	$options = array();

		//This HELPER does not exist on all Magento versions
		$helperClass = Mage::getConfig()->getHelperClassName('catalog/product_configuration');
		if( FALSE === class_exists($helperClass, FALSE) ){
			return $options;
		}

		$helper  = Mage::helper('catalog/product_configuration');
        if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
        	$options =  $helper->getCustomOptions($item);
        }elseif($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
        	$options =  $helper->getConfigurableOptions($item);
        }

        return $options;
    }

    protected function _getBasketContents($quote)
    {
		//WARNING: If want to import basket on SAGE software need to remove currency symbols\\

		if(false === $this->getSendBasket()){
			return null;
		}

		$itemsCollection = $quote->getItemsCollection();

		$orderCurrencyCode = ((string)$this->getConfigData('trncurrency') == 'store') ? $quote->getQuoteCurrencyCode() : $quote->getBaseCurrencyCode();

        $basket = '';

        $_currency = Mage::getModel('directory/currency')->load($orderCurrencyCode);

        if ($itemsCollection->getSize() > 0) {

            $numberOfdetailLines = $itemsCollection->getSize() + 1;
            $todelete = 0;

            foreach ($itemsCollection as $item) {
                if ($item->getParentItem()) { # Configurable products
                    $numberOfdetailLines--;
                }
            }

            $basket .= $numberOfdetailLines . self::BASKET_SEP;

            foreach ($itemsCollection as $item) {

				//Avoid duplicates SKUs on basket
				if( strpos($basket, ($this->_cleanString($item->getSku()).'|')) !== FALSE){
					continue;
				}
                if ($item->getParentItem()) {
                    continue;
                }

				$tax = ($item->getBaseTaxBeforeDiscount() ? $item->getBaseTaxBeforeDiscount() : ($item->getBaseTaxAmount() ? $item->getBaseTaxAmount() : 0));

				//Options
				$options = $this->_getProductOptions($item);
				$_options = '';
				if(count($options) > 0){
					foreach($options as $opt){
						$_options .= $opt['label'] . '-' . $opt['value'] .'.';
					}
					$_options = '_' . substr($_options, 0, -1) . '_';
				}

                $line = str_replace(':', '-', '[' . $this->_cleanString($item->getSku()) . ']|' . $this->_cleanString($item->getName())) . $this->_cleanString($_options) . self::BASKET_SEP;
                $line .= ( $item->getQty() * 1) . self::BASKET_SEP;
                $line .= $_currency->formatPrecision($item->getCalculationPrice(), 2, array(), false) . self::BASKET_SEP;
                $line .= $_currency->formatPrecision($item->getTaxAmount(), 2, array(), false) . self::BASKET_SEP;
                $line .= $_currency->formatPrecision($item->getTaxAmount()+$item->getPrice(), 2, array(), false) . self::BASKET_SEP;
                $line .= $_currency->formatPrecision((($item->getRowTotal()+$tax)-$item->getDiscountAmount()), 2, array(), false) . self::BASKET_SEP;

				if( strlen($basket.$line) < 7498 ){
					$basket .= $line;
				}else{
					$todelete++;
				}

            }
        }

		$deliveryName   = $quote->getShippingAddress()->getShippingDescription() ? $quote->getShippingAddress()->getShippingDescription() : 'Delivery';
		$deliveryValue  = $_currency->formatPrecision($quote->getShippingAddress()->getShippingAmount(), 2, array(), false);
		$deliveryTax    = $_currency->formatPrecision($quote->getShippingAddress()->getShippingTaxAmount(), 2, array(), false);
		$deliveryAmount = $_currency->formatPrecision($quote->getShippingAddress()->getShippingInclTax(), 2, array(), false);

        $delivery = $this->_cleanString($deliveryName) . self::BASKET_SEP . '1' . self::BASKET_SEP . $deliveryValue . self::BASKET_SEP . $deliveryTax . self::BASKET_SEP . $deliveryAmount . self::BASKET_SEP . $deliveryAmount;

		if( strlen($basket.$delivery) < 7498 ){
			$basket .= $delivery;
		}else{
			$todelete++;
		}

		$numberOfLines = substr($basket, 0, strpos($basket, ':'));

		if($todelete > 0){
			$num = $numberOfLines - $todelete;
			$basket = str_replace($numberOfLines, $num, $basket);
		}

		//Verify that items count is correct

		$items = explode(':', $basket);
        //Remove line number from basket
		array_shift($items);
        //Split into rows
        $rows = count(array_chunk($items, 6));
		if($rows != $numberOfLines){
			$basket = str_replace($numberOfLines, $rows, $basket);
		}

		//Verify that items count is correct

        return $basket;
    }

    protected function _getCoreHelper() {
        return Mage::helper('core');
    }

    protected function _sageHelper() {
        return Mage::helper('sagepaysuite');
    }

	/**
	 * Return Multishipping Checkout ACTIVE Step.
	 */
	public function getMsActiveStep()
	{
		return Mage::getSingleton('checkout/type_multishipping_state')->getActiveStep();
	}

	public function isMsOnOverview()
	{
		return ($this->_getQuote()->getIsMultiShipping() && $this->getMsActiveStep() == 'multishipping_overview');
	}

	protected function _getReservedOid()
	{

		if($this->isMsOnOverview() && ($this->_getQuote()->getPayment()->getMethod() == 'sagepayserver')){
			return null;
		}

		$orderId = $this->getSageSuiteSession()->getReservedOrderId();

		if (!$orderId) {

			// we need to check here if the orderId has already been used by other payment method
			if(!$this->_getQuote()->getReservedOrderId() || $this->_orderIdAlreadyUsed( $this->_getQuote()->getReservedOrderId() ) ){
				$this->_getQuote()->reserveOrderId()->save();
			}

            $orderId = $this->_getQuote()->getReservedOrderId();
            $this->getSageSuiteSession()->setReservedOrderId($orderId);

        }

		if($this->isMsOnOverview()){
			$this->getSageSuiteSession()->setReservedOrderId(null);
		}
		return $orderId;
	}

	protected function _orderIdAlreadyUsed($orderId)
	{
		// just in case there is no orderId provided
		if ( !$orderId ) return false;

		// let's check now if it has been used for another order
		$potentialExistingOrder = Mage::getModel( "sales/order" )->loadByIncrementId( $orderId );

		// if there is an order we should have it loaded by now
		if ( !$potentialExistingOrder->getId() ) {
			return false;
		}

		return true;
	}

	protected function _getTrnVendorTxCode()
	{
        $rsOid = $this->_getReservedOid();
        return ($rsOid) ? substr($rsOid . '-' . date('Y-m-d-H-i-s'), 0, 40) : substr(date('Y-m-d-H-i-s-').time(), 0, 40);
	}

	protected function _getRqParams()
	{
		return Mage::app()->getRequest()->getParams();
	}

	protected function _getBuildPaymentObject($quoteObj, $params = array('payment'=>array()))
	{
        $payment = new Varien_Object;
        if(isset($params['payment'])){
        	$payment->addData($params['payment']);
        }

        if(Mage::helper('sagepaysuite')->creatingAdminOrder()){
        	$payment->addData($quoteObj->getPayment()->toArray());
        }

		$billingAddressObj = $this->_getQuote()->getBillingAddress();
		$shippingAddressObj = $this->_getQuote()->getShippingAddress();

        $payment->setTransactionType(strtoupper($this->getConfigData('payment_action')));
        $payment->setAmountOrdered( $this->formatAmount($this->_getQuote()->getGrandTotal(), $this->_getQuote()->getQuoteCurrencyCode()) );
        $payment->setRealCapture(true); //To difference invoice from capture
        $payment->setOrder(new Varien_Object($this->_getQuote()->toArray()));
        $payment->setAnetTransType(strtoupper($this->getConfigData('payment_action')));
        $payment->getOrder()->setOrderCurrencyCode($this->_getQuote()->getQuoteCurrencyCode());
        $payment->getOrder()->setBillingAddress($this->_getQuote()->getBillingAddress());
        $payment->getOrder()->setShippingAddress($this->_getQuote()->getShippingAddress());

        return $payment;
	}

	public function isPayPalEnabled()
	{
		return ((int)Mage::getStoreConfig('payment/sagepaypaypal/active', Mage::app()->getStore()->getId()) === 1);
	}

	public function getPayPalMode()
	{
		return Mage::getStoreConfig('payment/sagepaypaypal/mode', Mage::app()->getStore()->getId());
	}

	protected function _getPayPalCallbackUrl()
	{
		return Mage::getModel('core/url')->addSessionParam()->getUrl('sgps/paypalexpress/callback', array('_secure' => true));
	}

	protected function _getPayPalRequest()
	{
        $quoteObj = $this->_getQuote();
		$paymentAction = Mage::getStoreConfig('payment/sagepaypaypal/payment_action', Mage::app()->getStore()->getId());
		$paypalVendor = (string)Mage::getStoreConfig('payment/sagepaypaypal/vendor', Mage::app()->getStore()->getId());

		$payment = $this->_getBuildPaymentObject($quoteObj);

		$rq = Mage::app()->getRequest()->getParam('quick');
		if($rq){//Button checkout
			$_grq = Mage::getModel('sagepaysuite/sagepaysuite_request');
		}else{//In checkout method
			$_grq = $this->_buildRequest($payment);
		}

		$request = $_grq
                ->setVPSProtocol((string)$this->getVpsProtocolVersion())
                ->setTxType($paymentAction)
                ->setVendor( ((strlen($paypalVendor)>1) ? $paypalVendor : $this->getConfigData('vendor')) )
                ->setVendorTxCode($this->_getTrnVendorTxCode())
                ->setCardType(self::CARD_TYPE_PAYPAL)
                ->setPayPalCallbackURL($this->_getPayPalCallbackUrl())
                ->setCustomerEMail($this->getCustomerLoggedEmail())
                ->setApplyAVSCV2('0')
                ->setClientIPAddress($this->getClientIp())
                ->setApply3DSecure('0')
                ->setGiftAidPayment('0')
                ->setAccountType('E')
                ->setReferrerID($this->getConfigData('referrer_id'))
                ->setInternalTxtype($paymentAction)
                ->setMode($this->getPayPalMode());

		if($this->getSendBasket()){
			$request->setBasket($this->_getBasketContents($quoteObj));
		}

        $this->_setRequestCurrencyAmount($request, $quoteObj);

		if(strlen($request->getDescription()) === 1){
			$request->setDescription('PayPal transaction.');
		}

        return $request;
	}

    protected function _setRequestCurrencyAmount($request, $quote)
    {

		if( $quote->getMacAmount() ){
			$quote = clone $quote;
			$quote->setGrandTotal($quote->getMacAmount());
			$quote->setBaseGrandTotal($quote->getBaseMacAmount());
		}

		if((string)$this->getConfigData('trncurrency') == 'store'){
        	$request->setAmount( $this->formatAmount($quote->getGrandTotal(), $quote->getQuoteCurrencyCode()) );
            $request->setCurrency($quote->getQuoteCurrencyCode());
        }else{
            $request->setAmount($this->formatAmount($quote->getBaseGrandTotal(), $quote->getBaseCurrencyCode()));
            $request->setCurrency($quote->getBaseCurrencyCode());
        }

    }

    public function saveAction($orderId, $request, $result)
    {
        $model = Mage::getModel('sagepaysuite2/sagepaysuite_action')->setParentId($orderId);

        $model->setStatus($result['Status'])
              ->setStatusDetail($result['StatusDetail'])
              ->setActionCode(strtolower($request['TxType']))
              ->setActionDate($this->getDate());

        if($request['TxType'] == self::REQUEST_TYPE_REFUND || $request['TxType'] == self::REQUEST_TYPE_RELEASE || $request['TxType'] == self::REQUEST_TYPE_AUTHORISE || $request['TxType'] == self::REQUEST_TYPE_REPEAT){
            $model->setVpsTxId((isset($result['VPSTxId']) ? $result['VPSTxId'] : null))
                  ->setTxAuthNo((isset($result['TxAuthNo']) ? $result['TxAuthNo'] : null))
                  ->setAmount((isset($result['Amount']) ? $result['Amount'] : null))
                  ->setCurrency((isset($request['Currency'])? $request['Currency'] : null));

			//For AUTHORISE requests
			if( !$model->getAmount() ){
				$model->setAmount((isset($request['Amount']) ? $request['Amount'] : null));
			}

			if($request['TxType'] == self::REQUEST_TYPE_AUTHORISE || $request['TxType'] == self::REQUEST_TYPE_REPEAT){
				$model->setSecurityKey($result['SecurityKey'])
                  ->setVendorTxCode($request['VendorTxCode']);
			}
        }

        return $model->save();
    }

    /**
     * Returns real payment method code.
     * @param string $dbName Name on db, direct/server
     * @return string Real module code
     */
    protected function _getIntegrationCode($dbName)
    {
        switch ($dbName) {
            case 'direct':
                return 'sagepaydirectpro';
                break;
            case 'server':
                return 'sagepayserver';
                break;
            case 'form':
                return 'sagepayform';
                break;
            default:
                return '';
                break;
        }
    }

    public function captureInvoice($payment, $amount)
    {
        $order = $payment->getOrder();
        $trn = $this->getTransactionDetails($order->getId());

        if(!$trn->getId()){
            $msg = $this->_getCoreHelper()->__('Transaction does not exist, order id -> %s', $order->getId());
            self::log($msg);
            throw new Mage_Core_Exception($msg);
        }

		/**
		 * If we are capturing a FORM transaction we HAVE to retrieve the
		 * SecurityKey from the "Admin & Access API"
		 */
		if(!$trn->getSecurityKey() && strtoupper($trn->getIntegration()) == 'FORM'){
			$this->_addSecurityKey($trn);
		}

		$payment->setTransactionId($trn->getId());

        if($trn->getTxType() == self::REQUEST_TYPE_AUTHENTICATE){
           $this->authorise($trn, $amount);
        }else if($trn->getTxType() == self::REQUEST_TYPE_DEFERRED){
           $this->release($trn, $amount);
        }

    }

    /**
     * Authorise AUTHENTICATED transaction
     * @param Varien_Object $trn
     * @param float $amount Amount to be authoprised
     *
     */
    public function authorise($trn, $amount)
    {
        $data = array();
    	$data['VPSProtocol']         = $trn->getVpsProtocol();
    	$data['TxType']              = self::REQUEST_TYPE_AUTHORISE;
    	$data['ReferrerID']          = $this->getConfigData('referrer_id');
    	$data['Vendor']              = $trn->getVendorname();
    	$data['VendorTxCode']        = substr(time(),0,30) . substr($trn->getVendorTxCode(), 0, 10);
        $data['Amount']              = $this->formatAmount($amount, $trn->getTrnCurrency());
        $data['Description']         = '.';
		$data['RelatedVPSTxId']      = $trn->getVpsTxId();
		$data['RelatedVendorTxCode'] = $trn->getVendorTxCode();
		$data['RelatedSecurityKey']  = $trn->getSecurityKey();

        $result = $this->requestPost($this->getUrl('authorise', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException($result['StatusDetail']);
        }

        $this->saveAction($trn->getOrderId(), $data, $result);

        $trn->setAuthorised(1)->save();
    }

	public function repeat($trn, $amount)
	{
        $data = array();
    	$data['VPSProtocol']         = $trn->getVpsProtocol();
    	$data['TxType']              = self::REQUEST_TYPE_REPEAT;
    	$data['ReferrerID']          = $this->getConfigData('referrer_id');
    	$data['Vendor']              = $trn->getVendorname();
    	$data['VendorTxCode']        = substr(time(),0,30) . substr($trn->getVendorTxCode(), 0, 10);
        $data['Amount']              = $this->formatAmount($amount, $trn->getTrnCurrency());
        $data['Currency']            = $trn->getTrnCurrency();
        $data['Description']         = '.';
		$data['RelatedVPSTxId']      = $trn->getVpsTxId();
		$data['RelatedVendorTxCode'] = $trn->getVendorTxCode();
		$data['RelatedSecurityKey']  = $trn->getSecurityKey();
		$data['RelatedTxAuthNo']     = $trn->getTxAuthNo();

        $result = $this->requestPost($this->getUrl('repeat', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);

        if($result['Status'] != 'OK'){
            //Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException('SagePay REPEAT error: ' . $result['StatusDetail']);
        }

		$result ['Amount'] = $data['Amount'];

        $this->saveAction($trn->getOrderId(), $data, $result);

		$result ['_requestvendor_'] = $data['VendorTxCode'];

        return $result;
	}

    public function release($trn, $amount)
    {
		/**
		 * If transaction is already RELEASED we need to REPEAT it.
		 */
    	if( !is_null($trn->getReleased()) ){
    		$this->repeat($trn, $amount);
    		return;
    	}

        $data = array();
    	$data['VPSProtocol']   = $trn->getVpsProtocol();
    	$data['TxType']        = self::REQUEST_TYPE_RELEASE;
    	$data['ReferrerID']    = $this->getConfigData('referrer_id');
    	$data['Vendor']        = $trn->getVendorname();
    	$data['VendorTxCode']  = $trn->getVendorTxCode();
        $data['VPSTxId']       = $trn->getVpsTxId();
        $data['SecurityKey']   = $trn->getSecurityKey();
        $data['TxAuthNo']      = $trn->getTxAuthNo();
        $data['ReleaseAmount'] = $this->formatAmount($amount, $trn->getTrnCurrency());

        $result = $this->requestPost($this->getUrl('release', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException('SagePay RELEASE error: ' . $result['StatusDetail']);
        }

		//For saving purposes
		$result ['Amount'] = $data['ReleaseAmount'];

        $this->saveAction($trn->getOrderId(), $data, $result);

        $trn->setReleased(1)->save();
    }

    /**
     * Refund Payment in SagePay
     * @param int $orderId Order ID
     * @param float $amount Amount to refund
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $trn = $this->getTransactionDetails($order->getId());

        if(!$trn->getId()){
            $msg = $this->_getCoreHelper()->__('Transaction does not exist, order id -> %s', $order->getId());
            self::log($msg);
            Mage::throwException($msg);
        }

		/**
		 * If we are REFUNDing a FORM transaction we HAVE to retrieve the
		 * SecurityKey from the "Admin & Access API"
		 */
		if(!$trn->getSecurityKey() && strtoupper($trn->getIntegration()) == 'FORM'){
			$this->_addSecurityKey($trn);
		}

		$paymentId = Mage::app()->getRequest()->getParam('sagepaysuiterefundtrn');
		if(!is_null($paymentId)){

			$action = Mage::getModel('sagepaysuite2/sagepaysuite_action')->load((int)$paymentId);

			if($action->getVpsTxId() && $action->getSecurityKey() && $action->getTxAuthNo()){
				$trn->setVpsTxId($action->getVpsTxId())
				    ->setSecurityKey($action->getSecurityKey())
				    ->setTxAuthNo($action->getTxAuthNo())
				    ->setVendorTxCode($action->getVendorTxCode());
			}

		}

		//TODO: If it is PAYPAL dont VOID

		//VOID payment if refunding full amount
		if( ((float)$order->getGrandTotal()) == $payment->getCreditmemo()->getGrandTotal() ){
			try{
				$this->voidPayment($trn);
				return $this;
			}catch(Exception $e){}
		}

        $data = array();
    	$data['VPSProtocol']         = $trn->getVpsProtocol();
    	$data['TxType']              = self::REQUEST_TYPE_REFUND;
    	$data['ReferrerID']          = $this->getConfigData('referrer_id');
    	$data['Vendor']              = $trn->getVendorname();
    	$data['VendorTxCode']        = substr(time(),0,30) . substr($trn->getVendorTxCode(), 0, 10);
        $data['Amount']              = $this->formatAmount($amount, $trn->getTrnCurrency());
        $data['Currency']            = $trn->getTrnCurrency();
        $data['Description']         = '.';
		$data['RelatedVPSTxId']      = $trn->getVpsTxId();
		$data['RelatedVendorTxCode'] = $trn->getVendorTxCode();
		$data['RelatedSecurityKey']  = $trn->getSecurityKey();
		$data['RelatedTxAuthNo']     = $trn->getTxAuthNo();
		//$data['RelatedSecurityKey']  = (isset($formSecKey) ? $formSecKey : $trn->getSecurityKey());


		if(strtoupper($trn->getTxType()) == self::REQUEST_TYPE_AUTHENTICATE){
			$lastAuthorise = Mage::getModel('sagepaysuite2/sagepaysuite_action')
			->getLastAuthorise($order->getId());

			if(is_null($lastAuthorise->getId())){
				Mage::throwException('AUTHORISE transaction not found. Refund online cannot be completed.');
			}

			$data['RelatedVPSTxId']     = $lastAuthorise->getVpsTxId();
			$data['RelatedVendorTxCode'] = $lastAuthorise->getVendorTxCode();
			$data['RelatedSecurityKey']  = $lastAuthorise->getSecurityKey();
			$data['RelatedTxAuthNo']     = $lastAuthorise->getTxAuthNo();
		}

        $result = $this->requestPost($this->getUrl('refund', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException($result['StatusDetail']);
        }

		//For saving purposes
		$result ['Amount'] = $data['Amount'];

        $this->saveAction($order->getId(), $data, $result);

        return $this;
    }


    /**
     * Cancel payment
     *  - DEFERRED  -> ABORT
	 *  - PAYMENT or RELEASE -> VOID
	 *  - REGISTERED or AUTHENTICATE -> CANCEL
     * @param   Varien_Object $invoicePayment
     * @return  Ebizmarts_SagePaySuite_Model_Api_Payment
     */
    public function cancelOrder(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        $trn = $this->getTransactionDetails($order->getId());

        if(!$trn->getId()){

        	$msg = $this->_getCoreHelper()->__('Sagepay local transaction does not exist, order id -> %s', $order->getId());
			$this->_getAdminSession()->addError($msg);

			self::log($msg);
            Mage::logException( new Exception($msg) );
        	return $this;

        }

		$t = strtoupper($trn->getTxType());

        if($t == self::REQUEST_TYPE_AUTHENTICATE){
            $this->_cancel($trn);
        }else if(($t == 'DEFERRED' && (int)$trn->getReleased()===1) || $t == 'PAYMENT'){
            $this->voidPayment($trn);
        }else if($t == 'DEFERRED'){
        	$this->abortPayment($trn);
        }

        return $this;
    }

    public function abortPayment($trn)
    {

		/**
		 * SecurityKey from the "Admin & Access API"
		 */
		if(!$trn->getSecurityKey() && strtoupper($trn->getIntegration()) == 'FORM'){
			$this->_addSecurityKey($trn);
		}

        $data = array();
    	$data['VPSProtocol']  = $trn->getVpsProtocol();
    	$data['TxType']       = self::REQUEST_TYPE_ABORT;
    	$data['ReferrerID']   = $this->getConfigData('referrer_id');
    	$data['Vendor']       = $trn->getVendorname();
    	$data['VendorTxCode'] = $trn->getVendorTxCode();
		$data['VPSTxId']      = $trn->getVpsTxId();
		$data['SecurityKey']  = $trn->getSecurityKey();
        $data['TxAuthNo']     = $trn->getTxAuthNo();

		try{
	        $result = $this->requestPost($this->getUrl('abort', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);
		}catch(Exception $e){
			Mage::throwException($this->_getHelper()->__('Transaction could not be aborted at SagePay. You may want to delete it from the local database and check the transaction at the SagePay admin panel.'));
		}

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException($result['StatusDetail']);
        }

		$this->saveAction($trn->getOrderId(), $data, $result);

        $trn->setAborted(1)->save();
    }

    public function voidPayment($trn)
    {

		/**
		 * SecurityKey from the "Admin & Access API"
		 */
		if(!$trn->getSecurityKey() && strtoupper($trn->getIntegration()) == 'FORM'){
			$this->_addSecurityKey($trn);
		}

        $data = array();
    	$data['VPSProtocol']  = $trn->getVpsProtocol();
    	$data['TxType']       = self::REQUEST_TYPE_VOID;
    	$data['ReferrerID']   = $this->getConfigData('referrer_id');
    	$data['Vendor']       = $trn->getVendorname();
    	$data['VendorTxCode'] = $trn->getVendorTxCode();
		$data['VPSTxId']      = $trn->getVpsTxId();
		$data['SecurityKey']  = $trn->getSecurityKey();
        $data['TxAuthNo']     = $trn->getTxAuthNo();

		try{
	        $result = $this->requestPost($this->getUrl('void', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);
		}catch(Exception $e){
			Mage::throwException($this->_getHelper()->__('Transaction could not be voided at SagePay. You may want to delete it from the local database and check the transaction at the SagePay admin panel.'));
		}

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException($result['StatusDetail']);
        }

		$this->saveAction($trn->getOrderId(), $data, $result);

        $trn->setVoided(1)->save();
    }

    private function _cancel($trn)
    {

		/**
		 * SecurityKey from the "Admin & Access API"
		 */
		if(!$trn->getSecurityKey() && strtoupper($trn->getIntegration()) == 'FORM'){
			$this->_addSecurityKey($trn);
		}

        $data = array();
    	$data['VPSProtocol']  = $trn->getVpsProtocol();
    	$data['TxType']       = self::REQUEST_TYPE_CANCEL;
    	$data['ReferrerID']   = $this->getConfigData('referrer_id');
    	$data['Vendor']       = $trn->getVendorname();
    	$data['VendorTxCode'] = $trn->getVendorTxCode();
		$data['VPSTxId']      = $trn->getVpsTxId();
		$data['SecurityKey']  = $trn->getSecurityKey();

        $result = $this->requestPost($this->getUrl('cancel', false, $this->_getIntegrationCode($trn->getIntegration()), $trn->getMode()), $data);

        if($result['Status'] != 'OK'){
            Ebizmarts_SagePaySuite_Log::w($result['StatusDetail']);
            Mage::throwException($result['StatusDetail']);
        }

		$this->saveAction($trn->getOrderId(), $data, $result);

        $trn->setCanceled(1)->save();
    }

    public function directCallBack3D(Varien_Object $payment, $PARes, $MD)
    {
        $error = '';

        $request= $this->_buildRequest3D($PARes, $MD);
        $result = $this->_postRequest($request, true);

		Sage_Log::log($result, null, '3D-Result.log');

        if ($result->getResponseStatus() == self::RESPONSE_CODE_APPROVED || $result->getResponseStatus() == 'AUTHENTICATED') {

            Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
            ->loadByVendorTxCode($this->getSageSuiteSession()->getLastVendorTxCode())
            ->setVpsProtocol($result->getData('VPSProtocol'))
            ->setSecurityKey($result->getData('SecurityKey'))
            ->setStatus($result->getData('Status'))
            ->setStatusDetail($result->getData('StatusDetail'))
            ->setVpsTxId($result->getData('VPSTxId'))
            ->setTxAuthNo($result->getData('TxAuthNo'))
            ->setAvscv2($result->getData('AVSCV2'))
            ->setPostcodeResult($result->getData('PostCodeResult'))
            ->setAddressResult($result->getData('AddressResult'))
            ->setCv2result($result->getData('CV2Result'))
            ->setThreedSecureStatus($result->getData('3DSecureStatus'))
            ->setCavv($result->getData('CAVV'))
            ->setTrndate($this->getDate())->save();

            $payment->setSagePayResult($result);

            $payment->setStatus(self::STATUS_APPROVED)
                    ->setCcTransId($result->getVPSTxId())
                    ->setCcApproval(self::RESPONSE_CODE_APPROVED)
                    ->setLastTransId($result->getVPSTxId())
                    ->setAddressResult($result->getAddressResult())
                    ->setPostcodeResult($result->getPostCodeResult())
                    ->setCv2Result($result->getCV2Result())
                    ->setSecurityKey($result->getSecurityKey())
                    ->setCcCidStatus($result->getTxAuthNo())
                    ->setAdditionalData($result->getResponseStatusDetail());
            $payment->save();

			if(strtoupper($this->getConfigData('payment_action')) == self::REQUEST_TYPE_PAYMENT){
                $this->getSageSuiteSession()->setInvoicePayment(true);
            }

			$quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
			$quote->collectTotals();
			return Mage::getSingleton('checkout/type_onepage')->saveOrder();

        }
        else {
        	//TODO: SAVE ORPHAN if 3D failed
            if ($result->getResponseStatusDetail()) {
                if ($result->getResponseStatus() == self::RESPONSE_CODE_NOTAUTHED) {
                    $error = $this->_sageHelper()->__('Your credit card can not be authenticated: ');
                } else if ($result->getResponseStatus() == self::RESPONSE_CODE_REJECTED) {
                    $error = $this->_sageHelper()->__('Your credit card was rejected: ');
                }
                $error .= $result->getResponseStatusDetail();
            }
            else {
                $error = $this->_sageHelper()->__('Error in capturing the payment');
            }
        }

        if (!empty($error)) {
            Mage::throwException($error);
        }
        return $this;
    }

  protected function _getAdminQuote()
  {
    return Mage::getSingleton('adminhtml/session_quote')->getQuote();
  }

    public function directRegisterTransaction(Varien_Object $payment, $amount)
    {
        #Process invoice
        if(!$payment->getRealCapture()){
            return $this->captureInvoice($payment, $amount);
        }

		/**
		 * Token Transaction
		 */
		if(true === $this->_tokenPresent()){
			$_info = new Varien_Object(array('payment'=>$payment));
			$result = $this->getTokenModel()->tokenTransaction($_info);

			if($result['Status'] != self::RESPONSE_CODE_APPROVED
				&& $result['Status'] != self::RESPONSE_CODE_3DAUTH
				&& $result['Status'] != self::RESPONSE_CODE_REGISTERED){
				Mage::throwException($result['StatusDetail']);
			}

            if(strtoupper($this->getConfigData('payment_action')) == self::REQUEST_TYPE_PAYMENT){
                $this->getSageSuiteSession()->setInvoicePayment(true);
            }

            $this->setSagePayResult($result);

            if($result['Status'] == self::RESPONSE_CODE_3DAUTH){
                $payment->getOrder()->setIsThreedWaiting(true);

                $this->getSageSuiteSession()->setSecure3dMethod('directCallBack3D');

                $this->getSageSuiteSession()
                        ->setAcsurl($result['ACSURL'])
                        ->setEmede($result['MD'])
                        ->setPareq($result['PAReq']);
                $this->setVndor3DTxCode($payment->getVendorTxCode());
            }

			return $this;
		}
		/**
		 * Token Transaction
		 */

        if($this->_getIsAdmin() && (int)$this->_getAdminQuote()->getCustomerId() === 0) {
            $cs = Mage::getModel('customer/customer')->setWebsiteId($this->_getAdminQuote()->getStoreId())->loadByEmail($this->_getAdminQuote()->getCustomerEmail());

            if( $cs->getId() ) {
                Mage::throwException($this->_SageHelper()->__('Customer already exists.'));
            }
        }
        if($this->_getIsAdmin()){
        	$payment->setRequestVendor( $this->getConfigData('vendor', $this->_getAdminQuote()->getStoreId()) );
        }

        if ($this->getSageSuiteSession()->getSecure3d()) {
            $this->directCallBack3D(
                    $payment,
                    $this->getSageSuiteSession()->getPares(),
                    $this->getSageSuiteSession()->getEmede());
            $this->getSageSuiteSession()->setSecure3d(null);
            return $this;
        }
        $this->getSageSuiteSession()->setMd(null)
                ->setAcsurl(null)
                ->setPareq(null);

        $error = false;

        $payment->setAnetTransType(strtoupper($this->getConfigData('payment_action')));

        $payment->setAmount($amount);

        $request= $this->_buildRequest($payment);

		Mage::dispatchEvent('sagepaysuite_direct_request_post_before', array('request' => $request, 'payment' =>$this));

        $result = $this->_postRequest($request);

        switch($result->getResponseStatus()) {
            case 'FAIL':
            	$error = $result->getResponseStatusDetail();
                $payment
                        ->setStatus('FAIL')
                        ->setCcTransId($result->getVPSTxId())
                        ->setLastTransId($result->getVPSTxId())
                        ->setCcApproval('FAIL')
                        ->setAddressResult($result->getAddressResult())
                        ->setPostcodeResult($result->getPostCodeResult())
                        ->setCv2Result($result->getCV2Result())
                        ->setCcCidStatus($result->getTxAuthNo())
                        ->setSecurityKey($result->getSecurityKey())
                        ->setAdditionalData($result->getResponseStatusDetail());
                break;
            case 'FAIL_NOMAIL':
                $error = $result->getResponseStatusDetail();
                break;
            case self::RESPONSE_CODE_APPROVED:
            case self::RESPONSE_CODE_REGISTERED:

                $payment->setSagePayResult($result);

                $payment
                        ->setStatus(self::RESPONSE_CODE_APPROVED)
                        ->setCcTransId($result->getVPSTxId())
                        ->setLastTransId($result->getVPSTxId())
                        ->setCcApproval(self::RESPONSE_CODE_APPROVED)
                        ->setAddressResult($result->getAddressResult())
                        ->setPostcodeResult($result->getPostCodeResult())
                        ->setCv2Result($result->getCV2Result())
                        ->setCcCidStatus($result->getTxAuthNo())
                        ->setSecurityKey($result->getSecurityKey());

				if(strtoupper($this->getConfigData('payment_action')) == self::REQUEST_TYPE_PAYMENT){
                	$this->getSageSuiteSession()->setInvoicePayment(true);
            	}

                break;
            case self::RESPONSE_CODE_PAYPAL_REDIRECT:

                $payment->setSagePayResult($result);

                break;
            case self::RESPONSE_CODE_3DAUTH:

                $payment->setSagePayResult($result);

                $payment->getOrder()->setIsThreedWaiting(true);

                $this->getSageSuiteSession()->setSecure3dMethod('directCallBack3D');

                $this->getSageSuiteSession()
                        ->setAcsurl($result->getData('a_cs_ur_l'))
                        ->setEmede($result->getData('m_d'))
                        ->setPareq($result->getData('p_areq'));
                $this->setVndor3DTxCode($payment->getVendorTxCode());

                break;
            default:
                if ($result->getResponseStatusDetail()) {
                    $error = '';
                    if ($result->getResponseStatus() == self::RESPONSE_CODE_NOTAUTHED) {

                    	$this->getSageSuiteSession()
                        ->setAcsurl(null)
                        ->setEmede(null)
                        ->setPareq(null);

                        $error = $this->_SageHelper()->__('Your credit card can not be authenticated: ');
                    } else if ($result->getResponseStatus() == self::RESPONSE_CODE_REJECTED) {
						$this->getSageSuiteSession()
                        ->setAcsurl(null)
                        ->setEmede(null)
                        ->setPareq(null);
                        $error = $this->_SageHelper()->__('Your credit card was rejected: ');
                    }
                    $error .= $result->getResponseStatusDetail();
                }
                else {
                    $error = $this->_SageHelper()->__('Error in capturing the payment');
                }
                break;
        }

        if ($error !== false) {

        	if( Mage::helper('adminhtml')->getCurrentUserId() !== FALSE ){
        		Mage::getSingleton('adminhtml/session')->addError($error);
        	}

            Mage::throwException($error);
        }

        return $this;
    }

	public function loadQuote($quoteId, $storeId)
	{
		return Mage::getModel('sales/quote')->setStoreId($storeId)->load($quoteId);
	}

	public function recoverTransaction($vendorTxCode)
	{
		$trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
	                ->loadByVendorTxCode($vendorTxCode);

		if(is_null($trn->getId())){
			Mage::throwException($this->_sageHelper()->__('Transaction "%s" not found.', $vendorTxCode));
		}

		if($trn->getStatus() != 'OK'){
			Mage::throwException($this->_sageHelper()->__('Transaction "%s" cannot be recovered, status MUST be "OK".', $vendorTxCode));
		}

		if(!is_null($trn->getOrderId())){
			Mage::throwException($this->_sageHelper()->__('Transaction "%s" is already associated to an order, no need to recover it.', $vendorTxCode));
		}
		$quote = $this->loadQuote($trn->getQuoteId(), $trn->getStoreId());
		if(!$quote->getId()){
			Mage::throwException($this->_sageHelper()->__('Quote could not be loaded for "%s".', $vendorTxCode));
		}

		Mage::register('Ebizmarts_SagePaySuite_Model_Api_Payment::recoverTransaction', $vendorTxCode);

		$o = Mage::getModel('sagepaysuite/createOrder', $quote)->create();

		return $o;
	}

	public function showPost()
	{
		$this->_code = 'direct';
		$showPostUrl = 'https://test.sagepay.com/showpost/showpost.asp';

		$data = array();
		$data ['SuiteModuleVersion'] = (string) Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/version');
		$data ['Vendor'] = uniqid();

		$this->requestPost($showPostUrl, $data, true);

		return $data['Vendor'];
	}

    /**
     * Send a post request with cURL
     * @param string $url URL to POST to
     * @param array $data Data to POST
     * @return array|string $result Result of POST
     */
    public function requestPost($url, $data, $returnRaw = false) {

        $storeId = $this->getStoreId();
        $aux = $data;

        if (isset($aux['CardNumber'])) {
            $aux['CardNumber'] = substr_replace($aux['CardNumber'], "XXXXXXXXXXXXX", 0, strlen($aux['CardNumber']) - 3);
        }
        if (isset($aux['CV2'])) {
            $aux['CV2'] = "XXX";
        }

        $rd = '';
        foreach ($data as $_key => $_val) {
            if ($_key == 'billing_address1')
                $_key = 'BillingAddress1';
            $rd .= $_key . '=' . urlencode(mb_convert_encoding($_val, 'ISO-8859-1', 'UTF-8')) . '&';
        }

        self::log($url, null, 'SagePaySuite_REQUEST.log');
        self::log(Mage::helper('core/http')->getHttpUserAgent(false), null, 'SagePaySuite_REQUEST.log');
        self::log($aux, null, 'SagePaySuite_REQUEST.log');

        $_timeout = (int)$this->getConfigData('connection_timeout');
        $timeout = ($_timeout > 0 ? $_timeout : 90);

        $output = array();

        $curlSession = curl_init();

		curl_setopt($curlSession, CURLOPT_USERAGENT, $this->_sageHelper()->getUserAgent());
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $rd);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

        $rawresponse = curl_exec($curlSession);

		if(true === $returnRaw){
			return $rawresponse;
		}

        self::log($rawresponse, null, 'SagePaySuite_RawResponse.log');

        //Split response into name=value pairs
        $response = explode(chr(10), $rawresponse);

        // Check that a connection was made
        if (curl_error($curlSession)) {

            self::log(curl_error($curlSession), Zend_Log::ALERT, 'SagePaySuite_REQUEST.log');
            self::log(curl_error($curlSession), Zend_Log::ALERT, 'Connection_Errors.log');

            $output['Status'] = 'FAIL';
            $output['StatusDetail'] = htmlentities(curl_error($curlSession)) . '. ' . $this->getConfigData('timeout_message');

            return $output;
        }

        curl_close($curlSession);

        // Tokenise the response
        for ($i = 0; $i < count($response); $i++) {
            // Find position of first "=" character
            $splitAt = strpos($response[$i], "=");
            // Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
            $arVal = (string) trim(substr($response[$i], ($splitAt + 1)));
            if (!empty($arVal)) {
                $output[trim(substr($response[$i], 0, $splitAt))] = $arVal;
            }
        }

        self::log($output, null, 'SagePaySuite_REQUEST.log');

        return $output;
    }

	public function getSendBasket()
	{
		return ((int)$this->getConfigData('send_basket') === 1 ? true : false);
	}

    protected function _getRequest() {
        return Mage::getModel('sagepaysuite/sagepaysuite_request');
    }

    protected function _buildRequest3D($PARes, $MD) {
        return $this->_getRequest()
                ->setMD($MD)
                ->setPARes($PARes);
    }

    public function getSageSuiteSession() {
        return Mage::getSingleton('sagepaysuite/session');
    }

    protected function _isInViewOrder() {
        $r = Mage::getModel('core/url')->getRequest();
        return (bool) ($r->getActionName() == 'view' && $r->getControllerName() == 'sales_order');
    }

    public function getTitle() {
        $mode = $this->getConfigData('mode');
        if ($mode == 'live' || $this->_isInViewOrder() === true || $this->getCode() == 'sagepaypaypal') {
            return parent::getTitle();
        }

        return parent::getTitle() . ' - ' . strtoupper($mode) . ' mode';
    }

    public function isServer() {
        return (bool) ($this->getCode() == 'sagepayserver');
    }

    public function isDirect() {
        return (bool) ($this->getCode() == 'sagepaydirectpro');
    }

    public function isMobile()
    {
    	return Mage::helper('sagepaysuite')->isMobileApp();
    }

    /**
     * Trim $string to certaing $length
     */
    public function ss($string, $length)
    {
    	return substr($string, 0, $length);
    }

	protected function _addSecurityKey($trn)
	{
		$trnDetails = Mage::getModel('sagepayreporting/sagepayreporting')->getTransactionDetails($trn->getVendorTxCode(), null);
		if ($trnDetails->getErrorcode() != '0000') {
			Mage::throwException($trnDetails->getError());
		}
		$formSecKey = (string)$trnDetails->getSecuritykey();
		$trn->setSecurityKey($formSecKey)
			->save();
	}

	/**
	 * Format amount based on currency
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return float|int
	 */
	public function formatAmount($amount, $currency)
	{
		$_amount = 0.00;

		//JPY, which only accepts whole number amounts
		if($currency == 'JPY'){
			$_amount = round($amount, 0, PHP_ROUND_HALF_EVEN);
		}else{
			$_amount = number_format($amount, 2, '.', '');
		}

		return $_amount;
	}

}
