<?php

/**
 * TOKEN main model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_SagePayToken extends Ebizmarts_SagePaySuite_Model_Api_Token
{

	protected $_code = 'sagepaytoken';
	protected $_formBlockType = 'sagepaysuite/form_sagePayToken';

	/*
	 * Check if the current logged in customer can add a new card.
	 * @return bool
	 */
    public function customerCanAddCard()
    {
    	$customerCards = Mage::helper('sagepaysuite/token')->loadCustomerCards()->getSize();
    	$maxCards = (int)Mage::getStoreConfig('payment/sagepaysuite/max_token_card');

		return ($customerCards < $maxCards);
    }

	public function getSessionCustomerId()
	{
	   return $this->getCustomerQuoteId();
	}
	/**
	 * Save registered token in local DB
	 */
	public function persistCard(array $info)
	{
        $sessId = $this->getSessionCustomerId();

		 $save = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')
	     ->setToken($info['Token'])
	     ->setStatus($info['Status'])
	     ->setVendor($info['Vendor'])
	     ->setCardType($info['CardType'])
	     ->setExpiryDate($info['ExpiryDate'])
	     ->setStatusDetail($info['StatusDetail'])
	     ->setProtocol($info['Protocol'])
	     ->setCustomerId($sessId)
	     ->setLastFour(substr($info['CardNumber'], -4));

         if(is_string($sessId)){
             $save->setVisitorSessionId($sessId);
         }

	     $save->save();

		 $this->getSageSuiteSession()->setLastSavedTokenccid($save->getId());

	     return $save;
	}

	/**
	 * Validate if token card is valid for checkout.
	 * @return bool
	 */
	public function isTokenValid($cardId)
	{
		$card = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')
				->load($cardId);

		if($card->getId()){
			if($card->getCustomerId() == $this->getSessionCustomerId() || $card->getVisitorSessionId() == $this->getSessionCustomerId()){
				return true;
			}
		}

		return false;
	}

	protected function _getNotificationUrl()
	{
	  return Mage::getUrl('sgps/card/registerPost', array('_secure'=>true, '_nosid'=>true))  . '?' . $this->getSidParam();
	}

	public function removeCard($token)
	{
		return $this->_postRemove($token);
	}

	public function tokenTransaction(Varien_Object $info)
	{
	  $sessT = $this->getSageSuiteSession()->getLastSavedTokenccid();
	  $tokenLoadId = (!is_null($sessT) ? $sessT : $info->getPayment()->getSagepayTokenCcId());

	  $_t = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($tokenLoadId);

	  $_t->setIsDefault(1)
	  ->save();

	  $isGuest = Mage::getSingleton('checkout/type_onepage')->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST;

	  $postData                   = array();
	  $postData                  += $this->_getGeneralTrnData($info->getPayment())->getData();
	  $postData['vendortxcode']   = $postData['vendor_tx_code'];
	  $postData['txtype']         = $info->getPayment()->getTransactionType();
	  $postData['InternalTxtype'] = $postData['txtype'];
	  $postData['token']          = $_t->getToken();
	  $postData['storetoken']     = ($isGuest ? '0' : '1');
	  $postData['description']    = '.';
	  $postData['CV2']            = $this->getSageSuiteSession()->getTokenCvv();
	  $postData['vendor']         = $this->getConfigData('vendor');

	  if(array_key_exists('integration', $postData) && strtolower($postData['integration']) == 'server'){
	  	$postData['Profile'] = 'LOW';
	  }

	  self::log($postData);

	  $postData = Mage::helper('sagepaysuite')->arrayKeysToCamelCase($postData);

	  if(isset($postData['Storetoken'])){
	  	$postData['StoreToken'] = $postData['Storetoken'];
	  	unset($postData['Storetoken']);
	  }

	  // Do not perform 3D checks on MS checkout
	  if($this->_isMultishippingCheckout()){
	  	$postData['Apply3DSecure'] = 2;
	  }

	  self::log($postData);

	  $urlPost = $this->getTokenUrl('post', (isset($postData['Integration']) ? $postData['Integration'] : 'direct'));

	  $rs = $this->requestPost($urlPost, $postData);
	  $rs['request'] = new Varien_Object($postData);

	  $objRs = new Varien_Object($rs);
	  $objRs->setResponseStatus($objRs->getData('Status'))
	  ->setResponseStatusDetail($objRs->getData('StatusDetail'));

	  $info->getPayment()->setSagePayResult($objRs);

	  return $rs;
	}

	public function registerCard(array $data = array(), $persist = false)
	{
        if($this->customerCanAddCard() === false){
        	return array('Status'=>'ERROR', 'StatusDetail'=>'You can\'t add more tokens. Please contact the administrator.');
        }

	  $postData = array();
	  $postData['VPSProtocol']      = $this->getVpsProtocolVersion();
	  $postData['TxType']           = 'TOKEN';
      $postData['Vendor']           = $this->getConfigData('vendor');
	  $postData['Currency']    	    = Mage::app()->getStore()->getCurrentCurrencyCode();
	  $postData['VendortxCode']  	= $this->getNewTxCode();
	  $postData['NotificationURL']	= $this->_getNotificationUrl();

	  if(array_key_exists('CardType', $data)){ #DIRECT

		$urlPost = $this->getTokenUrl('register', 'direct');
		$postData += $data;

	  }else{ #SERVER
	  	$urlPost = $this->getTokenUrl('register', 'server');
		$postData['Profile']          = 'LOW';
	  }

	  Sage_Log::log($urlPost);
	  Sage_Log::log($postData);

	  $result = $this->requestPost($urlPost, $postData);

	  if(true === $persist && $result['Status'] == 'OK'){
	   $this->persistCard($postData+=$result);
	  }

	  return $result;
	}

	/**
	 * Check if TOKEN is enabled on the configuration settings
	 * @return bool
	 */
	public function isEnabled()
	{
		return (bool)(Mage::getStoreConfig('payment/sagepaysuite/token_integration') != 'false');
	}

	protected function _postRemove($token)
	{
		$rqData = array();
		$rqData['VPSProtocol'] = $this->getVpsProtocolVersion();
		$rqData['TxType']      = 'REMOVETOKEN';
		$rqData['Vendor']      = $this->getConfigData('vendor');
		$rqData['Token']       = $token;

	  return $this->requestPost($this->getTokenUrl('removecard', 'direct'), $rqData);
	}

}