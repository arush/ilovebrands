<?php

/**
 * DIRECT main model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_SagePayDirectPro extends Ebizmarts_SagePaySuite_Model_Api_Payment
{

    protected $_code  = 'sagepaydirectpro';
    protected $_formBlockType = 'sagepaysuite/form_sagePayDirectPro';
    protected $_infoBlockType = 'sagepaysuite/info_sagePayDirectPro';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

	public function registerToken($payment)
	{
		if(true === $this->getTokenModel()->isEnabled()){
			$result = $this->getTokenModel()->registerCard($this->getNewTokenCardArray($payment), true);
			if($result['Status'] != 'OK'){
				Mage::throwException($result['StatusDetail']);
			}
			return $result;
		}
	}

	public function completePayPalTransaction(array $request, $quote)
	{
		$pdata = array();
		$pdata ['VPSProtocol']= $this->getVpsProtocolVersion();
		$pdata ['TxType']= 'COMPLETE';
		$pdata ['VPSTxId']= $request['VPSTxId'];

		if((string)$this->getConfigData('trncurrency') == 'store'){
        	$pdata ['Amount']= number_format($quote->getGrandTotal(), 2, '.', '');
        }else{
            $pdata ['Amount']= number_format($quote->getBaseGrandTotal(), 2, '.', '');
        }

		if($request['Status'] == parent::RESPONSE_CODE_PAYPAL_OK){
			$pdata ['Accept']= 'YES';
		}else{
			$pdata ['Accept']= 'NO';
		}

		$mode = Mage::getModel('sagepaysuite/sagePayPayPal')->getConfigData('mode');
		$_res  = $this->requestPost($this->getUrl('paypalcompletion', false, null, $mode), $pdata);

		$vtx = $this->getSageSuiteSession()->getLastVendorTxCode();
		$saveData = Mage::helper('sagepaysuite')->arrayKeysToUnderscore($_res);

		if($_res['Status'] == 'OK'){
			Mage::getModel('sagepaysuite2/sagepaysuite_paypaltransaction')
			->loadByVendorTxCode($vtx)
			->addData($saveData)
			->setVpsTxId($_res['VPSTxId'])
			->setTrndate(Mage::getModel('sagepaysuite/api_payment')->getDate())
			->save();

			Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
			->loadByVendorTxCode($vtx)
			->addData($saveData)
			->setPostcodeResult($_res['PostCodeResult'])
			->setVpsTxId($_res['VPSTxId'])
			->setThreedSecureStatus($_res['3DSecureStatus'])
			->save();


			$this->getSageSuiteSession()->setInvoicePayment(true);
		}else{
			Mage::throwException($_res['StatusDetail']);
		}

		Sage_Log::log($_res);

		return $_res;
	}

	/**
	 * Post PayPal transaction to SagePay.
	 */
	public function registerPayPalTransaction()
	{
		$_req = $this->_getPayPalRequest();
		$_res  = $this->_postRequest($_req, false);

		if($_res->getResponseStatus() != parent::RESPONSE_CODE_PAYPAL_REDIRECT){
			Mage::throwException($_res->getResponseStatusDetail());
		}

		$this->getSageSuiteSession()->setLastVendorTxCode($_req->getData('VendorTxCode'));

        $trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
            ->loadByVendorTxCode($_req->getData('VendorTxCode'))
            ->setVendorTxCode($_req->getData('VendorTxCode'))
            ->setVpsProtocol($_res->getData('VPSProtocol'))
            ->setVendorname($_req->getData('Vendor'))
            ->setMode($this->getPayPalMode())
            ->setTxType($_req->getData('InternalTxtype'))
            ->setTrnCurrency($_req->getCurrency())
            ->setIntegration('direct')
            ->setCardType($_req->getData('CardType'))

            ->setStatus($_res->getResponseStatus())
            ->setStatusDetail($_res->getResponseStatusDetail())
            ->setVpsTxId($_res->getData('VPSTxId'))
            ->setTrnCurrency($_req->getData('Currency'))
            ->setTrndate($this->getDate());

        $trn->save();

		return $_res;
	}

    /**
     * Register DIRECT transation.
     *
     * @param array $params
     * @param bool $onlyToken
     * @param float $macOrder MAC single order
     */
     public function registerTransaction($params = null, $onlyToken = false, $macOrder = null)
     {
        $quoteObj = $this->_getQuote();

		if(is_null($macOrder)){
			$amount = $this->_sageHelper()->moneyFormat($quoteObj->getGrandTotal());
		}else{
			$amount = $this->_sageHelper()->moneyFormat($macOrder->getGrandTotal());
			$baseAmount = $this->_sageHelper()->moneyFormat($macOrder->getBaseGrandTotal());
			$quoteObj->setMacAmount($amount);
			$quoteObj->setBaseMacAmount($baseAmount);
		}

		if(!is_null($params)){
			$payment = $this->_getBuildPaymentObject($quoteObj, $params);
		}else{
			$payment = $this->_getBuildPaymentObject($quoteObj);
		}

		if($onlyToken){
			return $this->registerToken($payment);
		}

        $_rs  = $this->directRegisterTransaction($payment, $amount);
        $_req = $payment->getSagePayResult()->getRequest();
        $_res = $payment->getSagePayResult();

		#Last order vendortxcode
		$this->getSageSuiteSession()->setLastVendorTxCode($_req->getData('VendorTxCode'));
		if($this->isMsOnOverview()){
			$tx = array();
			$regTxCodes = Mage::registry('sagepaysuite_ms_txcodes');
			if($regTxCodes){
				$tx += $regTxCodes;
				Mage::unregister('sagepaysuite_ms_txcodes');
			}
			$tx []= $_req->getData('VendorTxCode');
			Mage::register('sagepaysuite_ms_txcodes', $tx);
		}

        $trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
            ->loadByVendorTxCode($_req->getData('VendorTxCode'))
            ->setVendorTxCode($_req->getData('VendorTxCode'))
            ->setVpsProtocol($_res->getData('VPSProtocol'))
            ->setCustomerContactInfo($_req->getData('ContactNumber'))
            ->setCustomerCcHolderName($_req->getData('CustomerName'))
            ->setVendorname($_req->getData('Vendor'))
            ->setMode($this->getConfigData('mode'))
            ->setTxType($_req->getData('InternalTxtype'))
            ->setTrnCurrency($_req->getCurrency())
            ->setIntegration('direct')
            ->setCardType($_req->getData('CardType'))
            ->setCardExpiryDate($_req->getData('ExpiryDate'))
            ->setLastFourDigits(substr($_req->getData('CardNumber'), -4))

            ->setSecurityKey($_res->getData('SecurityKey'))
            ->setStatus($_res->getResponseStatus())
            ->setStatusDetail($_res->getResponseStatusDetail())
            ->setVpsTxId($_res->getData('VPSTxId'))
            ->setTxAuthNo($_res->getData('TxAuthNo'))
            ->setAvscv2($_res->getData('AVSCV2'))
            ->setPostcodeResult($_res->getData('PostCodeResult'))
            ->setAddressResult($_res->getData('AddressResult'))
            ->setCv2result($_res->getData('CV2Result'))
            ->setThreedSecureStatus($_res->getData('3DSecureStatus'))
            ->setCavv($_res->getData('CAVV'))
            ->setToken($_req->getData('Token'))
            ->setTrnCurrency($_req->getData('Currency'))
            ->setTrndate($this->getDate());

            $trn->save();

        return $_res;
    }

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
		$info = $this->getInfoInstance();

    	$tokenCardId = (int)$info->getSagepayTokenCcId();

		if($tokenCardId){
			$valid = $this->getTokenModel()->isTokenValid($tokenCardId);
			if(false === $valid){
				Mage::throwException($this->_getHelper()->__('Token card not valid. %s', $tokenCardId));
			}

			return $this;
		}

		if(Mage::getSingleton('sagepaysuite/session')->getSagepaypaypalRqpost() || !is_null(Mage::registry('Ebizmarts_SagePaySuite_Model_Api_Payment::recoverTransaction'))){
			return $this;
		}

        /*
        * calling parent validate function
        */

        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',', Mage::getStoreConfig('payment/sagepaydirectpro/cctypesSagePayDirectPro'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if ($ccNumber) {

            // ccNumber is not present after 3Dcallback, in this case we supose cc is already checked

        if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorCode = 'ccsave_expiration,ccsave_expiration_yr';
            $errorMsg = $this->_getHelper()->__('Incorrect credit card expiration date');
        }

            if (in_array($info->getCcType(), $availableTypes)) {
                if ($this->validateCcNum($ccNumber)
                        // Other credit card type number validation
                        || ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {

                    $ccType = 'OT';
                    $ccTypeRegExpList = array(
                            'VISA' => '/^4[0-9]{12}([0-9]{3})?$/', // Visa
                            'MC' => '/^5[1-5][0-9]{14}$/',       // Master Card
                            'AMEX' => '/^3[47][0-9]{13}$/'//,        // American Express
                            //'DI' => '/^6011[0-9]{12}$/'          // Discovery
                    );

                    foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                        if (preg_match($ccTypeRegExp, $ccNumber)) {
                            $ccType = $ccTypeMatch;
                            break;
                        }
                    }

                    if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
                        $errorCode = 'ccsave_cc_type,ccsave_cc_number';
                        $errorMsg = $this->_getHelper()->__("Credit card number mismatch with credit card type");
                    }
                }
                else {
                    $errorCode = 'ccsave_cc_number';
                    $errorMsg = $this->_getHelper()->__('Invalid Credit Card Number');
                }

            }
            else {
                $errorCode = 'ccsave_cc_type';
                $errorMsg = $this->_getHelper()->__('Credit card type is not allowed for this payment method');
            }
        }

        if($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;

        /*
        * calling parent validate function

        return parent::validate();*/
    }

    public function OtherCcType($type)
    {
        return $type=='OT' || $type=='SOLO' || $type=='DELTA' || $type=='UKE' || $type=='MAESTRO' || $type=='SWITCH' || $type == 'LASER' || $type == 'JCB' || $type == 'DC';
    }

    protected function _buildRequest(Varien_Object $payment)
    {
        $order = $payment->getOrder();

        $vendorTxCode = $this->_getTrnVendorTxCode();

        $payment->setVendorTxCode($vendorTxCode);

        $request = Mage::getModel('sagepaysuite/sagepaysuite_request')
                ->setVPSProtocol($this->getVpsProtocolVersion())
                ->setMode( ($payment->getRequestMode() ? $payment->getRequestMode() : $this->getConfigData('mode')) )
                ->setReferrerID($this->getConfigData('referrer_id'))
                ->setTxType($payment->getAnetTransType())
                ->setInternalTxtype($payment->getAnetTransType()) # Just for storing in transactions table
                ->setVendor(($payment->getRequestVendor() ? $payment->getRequestVendor() : $this->getConfigData('vendor')))
                ->setVendorTxCode($vendorTxCode)
                ->setDescription($this->ss(($payment->getCcOwner() ? $payment->getCcOwner() : '.'), 100))
                ->setClientIPAddress($this->getClientIp());

		if($this->getSendBasket()){
			$request->setBasket($this->_getBasketContents($this->_getQuote()));
		}

        if($request->getToken()){
        	$request->setData('store_token', 1);
        }

        if($this->_getIsAdminOrder()) {
            $request->setAccountType('M');
        }

        if($payment->getAmountOrdered()) {

            $this->_setRequestCurrencyAmount($request, $this->_getQuote());

        } else {
            Mage::throwException('No amount on payment');
            Ebizmarts_SagePaySuite_Log::w('No amount on payment');
        }

        if (!empty($order)) {

            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setBillingAddress($this->ss($billing->getStreet(1) . ' ' . $billing->getCity() . ' ' .
                        $billing->getRegion() . ' ' . $billing->getCountry(), 100)
                        )
                        ->setBillingSurname($this->ss($billing->getLastname(), 20))
                        ->setBillingFirstnames($this->ss($billing->getFirstname(), 20))
                        ->setBillingPostCode($this->ss($billing->getPostcode(),10))
                        ->setBillingAddress1($this->ss($billing->getStreet(1), 100))
                        ->setBillingAddress2($this->ss($billing->getStreet(2), 100))
                        ->setBillingCity($this->ss($billing->getCity(), 40))
                        ->setBillingCountry($billing->getCountry())
                        ->setCustomerName($this->ss($billing->getLastname() . ' ' . $billing->getFirstname(), 100))
                        ->setContactNumber(substr($this->_cphone($billing->getTelephone()),0,20))
                        ->setContactFax($billing->getFax());

                if ( $billing->getCountry() == 'US' ) {
                    $request->setBillingState($billing->getRegionCode());
                }

                $request->setCustomerEMail($this->ss($billing->getEmail(), 255));

            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setDeliveryAddress($shipping->getStreet(1) . ' ' . $shipping->getCity() . ' ' .
                        $shipping->getRegion() . ' ' . $shipping->getCountry()
                        )
                        ->setDeliverySurname($this->ss($shipping->getLastname(),20))
                        ->setDeliveryFirstnames($this->ss($shipping->getFirstname(),20))
                        ->setDeliveryPostCode($this->ss($shipping->getPostcode(),10))
                        ->setDeliveryAddress1($this->ss($shipping->getStreet(1),100))
                        ->setDeliveryAddress2($this->ss($shipping->getStreet(2),100))
                        ->setDeliveryCity($this->ss($shipping->getCity(),40))
                        ->setDeliveryCountry($shipping->getCountry());

                if ( $shipping->getCountry() == 'US' ) {
                    $request->setDeliveryState($shipping->getRegionCode());
                }
            } else {
                #If the cart only has virtual products, I need to put an shipping address to Sage Pay.
                #Then the billing address will be the shipping address to
                $request->setDeliveryAddress($billing->getStreet(1) . ' ' . $billing->getCity() . ' ' .
                        $billing->getRegion() . ' ' . $billing->getCountry()
                        )
                        ->setDeliverySurname($this->ss($billing->getLastname(),20))
                        ->setDeliveryFirstnames($this->ss($billing->getFirstname(),20))
                        ->setDeliveryPostCode($this->ss($billing->getPostcode(),10))
                        ->setDeliveryAddress1($this->ss($billing->getStreet(1),100))
                        ->setDeliveryAddress2($this->ss($billing->getStreet(2),100))
                        ->setDeliveryCity($billing->getCity())
                        ->setDeliveryCountry($billing->getCountry());

                if ( $billing->getCountry() == 'US' ) {
                    $request->setDeliveryState($billing->getRegionCode());
                }

            }

        }

        if($payment->getCcNumber()) {
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
        }else if($payment->getCcType() && ($payment->getCcType() == parent::CARD_TYPE_PAYPAL)){
        	$request->setCardType($payment->getCcType());
        	$request->setPayPalCallbackURL($this->_getPayPalCallbackUrl());
        }

        if (Mage::getSingleton('admin/session')->isLoggedIn() || $this->isMobile()) {
            $request->setApply3DSecure('2');
        } else if($this->_isMultishippingCheckout()) {
            $request->setApply3DSecure('2');
        } else {
            $request->setApply3DSecure($this->getConfigData('secure3d'));
        }

        if($request->getAccountType() != 'M' && $this->_forceCardChecking($payment->getCcType()) === true){
        	$request->setApply3DSecure('3');
        }

        $request->setData('ApplyAVSCV2', $this->getConfigData('avscv2'));

		if($payment->getCcGiftaid() == 1 || $payment->getCcGiftaid() == 'on'){
			$request->setData('GiftAidPayment', 1);
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
	 * Force 3D secure checking based on card rule
	 */
	protected function _forceCardChecking($ccType = null)
	{
		$config = $this->getConfigData('force_threed_cards');

		if(is_null($ccType) || strlen($config) === 0){
			return false;
		}

		$config = explode(',', $config);
		if(in_array($ccType, $config)){
			return true;
		}

		return false;
	}

    protected function _postRequest(Varien_Object $request, $callback3D = false)
    {

        $result = Mage::getModel('sagepaysuite/sagepaysuite_result');

		$mode = (($request->getMode()) ? $request->getMode() : null);

        $uri = $this->getUrl('post', $callback3D, null, $mode);

        $requestData = $request->getData();

        try {

            $response = $this->requestPost($uri, $request->getData());

        } catch (Exception $e) {
            $result->setResponseCode(-1)
                    ->setResponseReasonCode($e->getCode())
                    ->setResponseReasonText($e->getMessage());

            Mage::throwException(
                    $this->_SageHelper()->__('Gateway request error: %s', $e->getMessage())
            );

        }

        $r = $response;


        $result->setRequest($request);

        try {
            if(empty($r)) {
                $msg = $this->_SageHelper()->__('Sage Pay is not available at this time. Please try again later.');
                Ebizmarts_SagePaySuite_Log::w($msg, 1);
                $result
                        ->setResponseStatus('ERROR')
                        ->setResponseStatusDetail($msg);
                return $result;
            }

            switch($r['Status']) {
                case 'FAIL':
                    $params['order'] = Mage::getSingleton('checkout/session')->getQuote()->getReservedOrderId();
                    $params['error'] = $r['StatusDetail'];
                    $rc = $this->sendNotificationEmail('', '', $params);

                    $result->setResponseStatus($r['Status'])
                            ->setResponseStatusDetail($r['StatusDetail'])  // to store
                            ->setVPSTxID(1)    // to store
                            ->setSecurityKey(1) // to store
                            ->setTxAuthNo(1)
                            ->setAVSCV2(1)
                            ->setAddressResult(1)
                            ->setPostCodeResult(1)
                            ->setCV2Result(1)
                            ->setTrnSecuritykey(1);
                    return $result;
                    break;
                case 'FAIL_NOMAIL':
                    Mage::throwException($this->_SageHelper()->__($r['StatusDetail']));
                    break;
                case parent::RESPONSE_CODE_INVALID:
                case parent::RESPONSE_CODE_MALFORMED:
                case parent::RESPONSE_CODE_ERROR:
                case parent::RESPONSE_CODE_REJECTED:
                    Mage::throwException($this->_SageHelper()->__('Error in payment. Sagepay says: %s', $r['StatusDetail']));
                    break;
                case parent::RESPONSE_CODE_3DAUTH:
                    $result->setResponseStatus($r['Status'])
                            ->set3DSecureStatus($r['3DSecureStatus'])    // to store
                            ->setMD($r['MD']) // to store
                            ->setACSURL($r['ACSURL'])
                            ->setPAReq($r['PAReq']);
                    break;
                case parent::RESPONSE_CODE_PAYPAL_REDIRECT:
                    $result->setResponseStatus($r['Status'])
                            ->setResponseStatusDetail($r['StatusDetail'])
                            ->setVpsTxId($r['VPSTxId'])
                            ->setPayPalRedirectUrl($r['PayPalRedirectURL']);
                    break;
                default:

                    $result->setResponseStatus($r['Status'])
                            ->setResponseStatusDetail($r['StatusDetail'])  // to store
                            ->setVpsTxId($r['VPSTxId'])    // to store
                            ->setSecurityKey($r['SecurityKey']) // to store
                            ->setTrnSecuritykey($r['SecurityKey']);
                    if (isset($r['3DSecureStatus']))
                        $result->set3DSecureStatus($r['3DSecureStatus']);
                    if (isset($r['CAVV']))
                        $result->setCAVV($r['CAVV']);

                    if(isset($r['TxAuthNo']))
                        $result->setTxAuthNo($r['TxAuthNo']);
                    if(isset($r['AVSCV2']))
                        $result->setAvscv2($r['AVSCV2']);
                    if(isset($r['PostCodeResult']))
                        $result->setPostCodeResult($r['PostCodeResult']);
                    if(isset($r['CV2Result']))
                        $result->setCv2result($r['CV2Result']);
                    if(isset($r['AddressResult']))
                        $result->setAddressResult($r['AddressResult']);

                    $result->addData($r);

                    break;

            }

        } catch (Exception $e) {

            Sage_Log::logException($e);

            $result
                    ->setResponseStatus('ERROR')
                    ->setResponseStatusDetail($e->getMessage());
            return $result;

        }

        return $result;
    }

	public function getNewTokenCardArray(Varien_Object $payment)
	{
		$data = array();
		$data ['CardHolder']=  $payment->getCcOwner();
		$data ['CardNumber']=  $payment->getCcNumber();
		$data ['CardType']=    $payment->getCcType();
		$data ['Currency']=    $payment->getOrder()->getOrderCurrencyCode();
		$data ['CV2']=         $payment->getCcCid();
		$data ['Protocol']=    'direct'; #For persistant storing
		$data ['ExpiryDate'] = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT) . substr($payment->getCcExpYear(), 2);
        if($payment->getCcSsStartMonth() && $payment->getCcSsStartYear()){
            $data['StartDate'] = str_pad($payment->getCcSsStartMonth(), 2, '0', STR_PAD_LEFT) . substr($payment->getCcSsStartYear(), 2);
        }

		return $data;
	}

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        #Process invoice
        if(!$payment->getRealCapture()){
            return $this->captureInvoice($payment, $amount);
        }

		/**
		 * Token Transaction
		 */
		if(true === $this->_tokenPresent()/* || $this->_getSageSuiteSession()->getLastSavedTokenccid()*/){
			$_info = new Varien_Object(array('payment'=>$payment));
			$result = $this->getTokenModel()->tokenTransaction($_info);
			if($result['Status'] != 'OK'){
				Mage::throwException($result['StatusDetail']);
			}

            $this->getSageSuiteSession()->setInvoicePayment(true);

			$this->setSagePayResult($result);
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

        if ($this->getSageSuiteSession()->getSecure3d()) {
            $this->capture3D(
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

        $payment->setAnetTransType(parent::REQUEST_TYPE_PAYMENT);

        $payment->setAmount($amount);

        $request= $this->_buildRequest($payment);
        $result = $this->_postRequest($request);
        switch($result->getResponseStatus()) {
            case 'FAIL':
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
            case parent::RESPONSE_CODE_APPROVED:

                $payment->setSagePayResult($result);

                $payment
                        ->setStatus(parent::RESPONSE_CODE_APPROVED)
                        ->setCcTransId($result->getVPSTxId())
                        ->setLastTransId($result->getVPSTxId())
                        ->setCcApproval(parent::RESPONSE_CODE_APPROVED)
                        ->setAddressResult($result->getAddressResult())
                        ->setPostcodeResult($result->getPostCodeResult())
                        ->setCv2Result($result->getCV2Result())
                        ->setCcCidStatus($result->getTxAuthNo())
                        ->setSecurityKey($result->getSecurityKey());

                $this->getSageSuiteSession()->setInvoicePayment(true);

                break;
            case parent::RESPONSE_CODE_3DAUTH:

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
                    if ($result->getResponseStatus() == parent::RESPONSE_CODE_NOTAUTHED) {
                        $error = $this->_SageHelper()->__('Your credit card can not be authenticated: ');
                    } else if ($result->getResponseStatus() == parent::RESPONSE_CODE_REJECTED) {
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
            Mage::throwException($error);
        }
        return $this;
    }

     public function saveOrderAfter3dSecure($pares, $md)
     {

        $this->getSageSuiteSession()->setSecure3d(true);
        $this->getSageSuiteSession()->setPares($pares);
        $this->getSageSuiteSession()->setMd($md);

		$quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
		$order = $this->directCallBack3D($quote->getPayment(), $pares, $md);

        $this->getSageSuiteSession()
                                             ->setAcsurl(null)
                                             ->setPareq(null)
                                             ->setSageOrderId(null)
                                             ->setSecure3d(null)
                                             ->setEmede(null)
                                             ->setPares(null)
                                             ->setMd(null);

        return $order;
    }

    public function sendNotificationEmail($toEmail = '', $toName = '', $params = array())
    {
      $translate = Mage::getSingleton('core/translate');

      $translate->setTranslateInline(false);

      $storeId = $this->getStoreId();

      if ($this->getWebsiteId() != '0' && $storeId == '0') {
        $storeIds = Mage::app()->getWebsite($this->getWebsiteId())->getStoreIds();
        reset($storeIds);
        $storeId = current($storeIds);
      }
  $toEmail = Mage::getStoreConfig('trans_email/ident_support/email', $storeId);
  $toName = Mage::getStoreConfig('trans_email/ident_support/name', $storeId);


      $mail = Mage::getModel('core/email_template')
      ->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
      ->sendTransactional(
                Mage::getStoreConfig('payment/sagepaydirectpro/email_timeout_template'),
                array('name' => Mage::getStoreConfig('trans_email/ident_general/name', $storeId), 'email'=>Mage::getStoreConfig('trans_email/ident_general/email', $storeId)),
//                Mage::getStoreConfig('payment/sagepaydirectpro/email_timeout_identity'),
                $toEmail,
                $toName,
                $params);

      $translate->setTranslateInline(true);

      return $mail->getSentSuccess();
    }

    public function getOrderPlaceRedirectUrl()
    {
        $tmp = $this->getSageSuiteSession();

        Ebizmarts_SagePaySuite_Log::w($tmp->getAcsurl().'-'.$tmp->getEmede().'-'.$tmp->getPareq());

        if ( $tmp->getAcsurl() && $tmp->getEmede() && $tmp->getPareq()) {
            #return Mage::getUrl('sagepaydirectpro/payment/redirect', array('_secure' => true));
            return Mage::getUrl('sagepaydirectpro-3dsecure', array('_secure' => true));
        } else {
            return false;
        }
    }

	public function getPayPalTitle()
	{
		return Mage::getStoreConfig('payment/sagepaypaypal/title', Mage::app()->getStore()->getId());
	}

    /**
     * @return array
     */
    public function getConfigSafeFields()
    {
        return array('active', 'mode', 'title', 'useccv', 'threed_iframe_height', 'threed_iframe_width');
    }

}

