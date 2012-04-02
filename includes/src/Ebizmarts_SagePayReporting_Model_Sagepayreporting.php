<?php

class Ebizmarts_SagePayReporting_Model_SagePayReporting extends Mage_Core_Model_Abstract
{

	protected $_vendor;
	protected $_username;
	protected $_password;
	protected $_enviroment;

	public function _construct()
	{
		parent::_construct();
		$this->_init('sagepayreporting/sagepayreporting');
		$this->_setConfiguration();
	}

	protected function _getRepStoreId()
	{
		return Mage::registry('reporting_store_id');
	}

	/**
	 * Sets the service configurations.
	 *
	 * @return void
	 */
	private function _setConfiguration()
	{
		$_configuration = Mage::app()->getStore($this->_getRepStoreId())->getConfig('sagepayreporting/account');
		$this->_vendor = $_configuration['vendor'];
		$this->_username = $_configuration['username'];
		$this->_password = Mage::helper('core')->decrypt($_configuration['password']);
		$this->_enviroment = $_configuration['enviroment'];
	}

	/**
	 * Returns url for each enviroment according the configuration.
	 *
	 * @param integer $url Url id.
	 * @return  string      Url for enviroment.
	 */
	private function _getServiceUrl($url)
	{
		switch($url) {
			case 1:
				return 'https://live.sagepay.com/access/access.htm';
				break;
			case 2:
				return 'https://test.sagepay.com/access/access.htm';
				break;
		}
	}

	/**
	 * Creates the connection's signature.
	 *
	 * @param string $command Param request to the API.
	 * @return string MD5 hash signature.
	 */
	private function _getXmlSignature($command, $params)
	{
		$xml = '<command>' . $command . '</command>';
		$xml .= '<vendor>' . $this->_vendor . '</vendor>';
		$xml .= '<user>' . $this->_username . '</user>';
		$xml .= $params;
		$xml .= '<password>' . $this->_password . '</password>';

		return md5($xml);
	}

	/**
	 * Creates the xml file to be used into the request.
	 *
	 * @param string $command API command.
	 * @param string $params  Parameters used for each command.
	 * @return string Xml string to be used into the API connection.
	 */
	private function _createXml($command, $params = null)
	{
		$xml = '';
		$xml .= '<vspaccess>';
		$xml .= '<command>' . $command . '</command>';
		$xml .= '<vendor>' . $this->_vendor . '</vendor>';
		$xml .= '<user>' . $this->_username . '</user>';

		if(!is_null($params)){
			$xml .= $params;
		}

		$xml .= '<signature>' . $this->_getXmlSignature($command, $params) . '</signature>';
		$xml .= '</vspaccess>';
		return $xml;
	}

	public function addValidIPs($vendor, $address, $mask, $note, $mode, $apiuser = '', $apipassword = '')
	{
		$this->_enviroment = $mode;
		$this->_vendor = $vendor;

		if(!empty($apiuser)){
			$this->_username = $apiuser;
		}
		if(!empty($apipassword)){
			$this->_password = $apipassword;
		}

		$params = '<validips><ipaddress><address>'.$address.'</address>';
		$params .= '<mask>'.$mask.'</mask><note>'.$note.'</note></ipaddress></validips>';

		$xml = $this->_createXml('addValidIPs', $params);

		return $this->_executeRequest($xml);
	}

	/**
	 * Returns all information held in the database about the specified transaction.
	 * Only a transaction associated with the vendor can be returned.
	 * Transactions can be specified by either VendorTxCode or VPSTxID.
	 *
	 * This command does not require the user to have administrator rights, but only
	 * those transactions that can be viewed by the user account will be returned.
	 *
	 * @param string $vendortxcode The VendorTxCode of the transaction.
	 * @param string $vpstxid      The VPSTxID (transactionid) of the transaction.
	 * @return object Xml object with the transaction details.
	 */
	public function getTransactionDetails($vendortxcode, $vpstxid = null)
	{
		$trnModel = Mage::getModel('sagepaysuite2/sagepaysuite_transaction');

		if(is_null($vpstxid)){
			$trn = $trnModel->loadByVendorTxCode($vendortxcode);
			$params = '<vendortxcode>' . $vendortxcode . '</vendortxcode>';
		}else{
			$trn = $trnModel->loadByVpsTxId($vpstxid);
			$params = '<vpstxid>' . $vpstxid . '</vpstxid>';
		}

		if($trn->getOrderId()){
			Mage::unregister('reporting_store_id');
			Mage::register('reporting_store_id', Mage::getModel('sales/order')->load($trn->getOrderId())->getStoreId());
			$this->_setConfiguration();
		}

		if($trn->getVendorname() && ($trn->getVendorname() != $this->_vendor)){
			$this->_vendor = $trn->getVendorname();
		}

		$xml = $this->_createXml('getTransactionDetail', $params);
		$api_response = $this->_executeRequest($xml);
		$response = $this->_mapTranscationDetails($api_response);
		return $response;
	}

	public function basicOperation($opname, $params = null)
	{
		$xml = $this->_createXml($opname, $params);
		$api_response = $this->_executeRequest($xml);

		if ((string)$api_response->errorcode !== '0000'){
			Mage::throwException(htmlentities((string)$api_response->error));
		}
		return $api_response;
	}

	public function getValidIPs()
	{
		return $this->basicOperation('getValidIPs');
	}

	public function getAvsCv2Rules()
	{
		return $this->basicOperation('getAVSCV2Rules');
	}

	public function getAVSCV2Status()
	{
		return $this->basicOperation('getAVSCV2Status');
	}

	public function setAVSCV2Status($status)
	{
		return $this->basicOperation( 'setAVSCV2Status', ('<status>' . $status . '</status>') );
	}

	public function get3dSecureRules()
	{
		return $this->basicOperation('get3DSecureRules');
	}

	public function get3dSecureStatus()
	{
		return $this->basicOperation('get3DSecureStatus');
	}

	public function set3dSecureStatus($status)
	{
		return $this->basicOperation( 'set3DSecureStatus', ('<status>' . $status . '</status>') );
	}

	public function getTokenCount()
	{
		return $this->basicOperation('getTokenCount');
	}

	public function getRelatedTransactions($vpstxid, $startDate = null)
	{
		if(is_null($startDate)){
			$startDate = Mage::getModel('core/date')->date('d/m/Y 00:00:00', strtotime('-1 year'));
		}

		$params = '<vpstxid>' . $vpstxid . '</vpstxid>';
		$params .= '<startdate>' . $startDate . '</startdate>';
		return $this->basicOperation('getRelatedTransactions', $params);
	}

	/**
	 * Return The 3RD man breakdown
	 * @param string Thirdman ID
	 * @return mixed
	 */
	public function getT3MDetail($t3mid)
	{
		$params = '<t3mtxid>' . $t3mid . '</t3mtxid>';
		return $this->basicOperation('getT3MDetail', $params);
	}

	/**
	 * Convert xml object to a Varien object.
	 *
	 * @param SimpleXMLElement $xml Xml response from the API.
	 * @return object Api response into a Varien object.
	 */
	private function _mapTranscationDetails(SimpleXMLElement $xml) {
		$object = new Varien_Object;
		$object->setErrorcode($xml->errorcode);
		$object->setTimestamp($xml->timestamp);
		if ((string)$xml->errorcode === '0000') {
			$object->setVpstxid($xml->vpstxid);
			$object->setContactNumber($xml->contactnumber);
			$object->setVendortxcode($xml->vendortxcode);
			$object->setTransactiontype($xml->transactiontype);
			$object->setStatus($xml->status);
			$object->setDescription($xml->description);
			$object->setAmount($xml->amount);
			$object->setCurrency($xml->currency);
			$object->setStarted($xml->started);
			$object->setCompleted($xml->completed);
			$object->setSecuritykey($xml->securitykey);
			$object->setClientip($xml->clientip);
			$object->setIplocation($xml->iplocation);
			$object->setGiftaid($xml->giftaid);
			$object->setPaymentsystem($xml->paymentsystem);
			$object->setPaymentsystemdetails($xml->paymentsystemdetails);
			$object->setAuthprocessor($xml->authprocessor);
			$object->setMerchantnumber($xml->merchantnumber);
			$object->setAccounttype($xml->accounttype);
			$object->setBillingaddress($xml->billingaddress);
			$object->setBillingpostcode($xml->billingpostcode);
			$object->setDeliveryaddress($xml->deliveryaddress);
			$object->setDeliverypostcode($xml->deliverypostcode);
			$object->setSystemused($xml->systemused);
			$object->setCustomeremail($xml->customeremail);
			$object->setAborted($xml->aborted);
			$object->setRefunded($xml->refunded);
			$object->setRepeated($xml->repeated);
			$object->setBasket($xml->basket);
			$object->setApplyavscv2($xml->applyavscv2);
			$object->setApply3dsecure($xml->apply3dsecure);
			$object->setAttempt($xml->attempt);
			$object->setCardholder($xml->cardholder);
			$object->setCardaddress($xml->cardaddress);
			$object->setCardpostcode($xml->cardpostcode);
			$object->setStartdate($xml->startdate);
			$object->setExpirydate($xml->expirydate);
			$object->setLast4digits($xml->last4digits);
			$object->setThreedresult($xml->threedresult);
			$object->setEci($xml->eci);
			$object->setCavv($xml->cavv);
			$object->setT3mscore($xml->t3mscore);
			$object->setT3maction($xml->t3maction);
			$object->setT3mid($xml->t3mid);
		} else {
			$object->setError(htmlentities($xml->error));
		}
		return $object;
	}

	/**
	 * Makes the Curl call and returns the xml response.
	 *
	 * @param string $xml description
	 */
	private function _executeRequest($xml)
	{
		$url = $this->_getServiceUrl($this->_enviroment);

		//Ebizmarts_SagePaySuite_Log::w($url, null, 'SagePay_Reporting.log');

		// Initialise output variable
		$output = array();

		$curlSession = curl_init();

		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_HEADER, 0);
		curl_setopt($curlSession, CURLOPT_POST, 1);
		curl_setopt($curlSession, CURLOPT_POSTFIELDS, 'XML='.$xml);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlSession, CURLOPT_TIMEOUT, 120);
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

		$rawresponse = curl_exec($curlSession);
		// Check that a connection was made
		if (curl_error($curlSession)) {
			Mage::throwException(curl_error($curlSession));
		}

		//Ebizmarts_SagePaySuite_Log::w($xml, null, 'SagePay_Reporting.log');
		//Ebizmarts_SagePaySuite_Log::w($rawresponse, null, 'SagePay_Reporting.log');

		// Close the cURL session
		curl_close ($curlSession);
		$xml = simplexml_load_string($rawresponse);

		return $xml;
	}

}
