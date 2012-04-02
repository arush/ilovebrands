<?php

/**
 * FORM main model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_SagePayForm extends Ebizmarts_SagePaySuite_Model_Api_Payment
{

    protected $_code  = 'sagepayform';
    protected $_formBlockType = 'sagepaysuite/form_sagePayForm';
    protected $_infoBlockType = 'sagepaysuite/info_sagePayForm';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

	public function validate() {
		Mage_Payment_Model_Method_Abstract::validate();
		return $this;
	}

	public function isAvailable($quote = null) {
		return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
	}

	public function simpleXor($InString, $Key)
	{
	  $KeyList = array();
	  $output = "";

	  // Convert $Key into array of ASCII values
	  for($i = 0; $i < strlen($Key); $i++){
	    $KeyList[$i] = ord(substr($Key, $i, 1));
	  }

	  for($i = 0; $i < strlen($InString); $i++) {
	    // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
	    // % is MOD (modulus), ^ is XOR
	    $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
	  }

	  return $output;
	}

	/**
	 * Return decrypted "encryption pass" from DB
	 */
    public function getEncryptionPass()
    {
        return Mage::helper('core')->decrypt($this->getConfigData('encryption_pass'));
    }

	public function base64Decode($scrambled)
	{
	  $output = "";
	  // Fix plus to space conversion issue
	  $scrambled = str_replace(" ","+",$scrambled);
	  $output = base64_decode($scrambled);
	  return $output;
	}

	public function decrypt($strCrypt)
	{
		$cryptPass = $this->getEncryptionPass();

		return $this->simpleXor($this->base64Decode($strCrypt),$cryptPass);
	}

	public function makeCrypt() {

		$cryptPass = $this->getEncryptionPass();

		if(Zend_Validate::is($cryptPass , 'NotEmpty') === false){
			Mage::throwException('Encryption Pass is empty.');
		}

		$quoteObj = $this->_getQuote();

		$quote = $quoteObj->getData();

		$billing = $quoteObj->getBillingAddress();
		$shipping = $quoteObj->getShippingAddress();
		$quoteItems = $quoteObj->getItemsCollection()->getData();
		$totals = $shipping->getTotals();

		/*try {
			$rsOid = $quoteObj->reserveOrderId()->getReservedOrderId();
		} catch (Exception $e) {
			$rsOid = '';
		}*/

		$rsOid = '';

		$customerEmail = $this->getCustomerEmail();

		$data = array ();

		$data['CustomerEMail'] = ($customerEmail == null ? $billing->getEmail() : $customerEmail);
		$data['CustomerName'] = $billing->getFirstname() . ' ' . $billing->getLastname();
		$data['VendorTxCode'] = $this->_getTrnVendorTxCode();

		if((string)$this->getConfigData('trncurrency') == 'store'){
			$data['Amount'] = number_format($quoteObj->getGrandTotal(), 2, '.', '');
			$data['Currency'] = $quoteObj->getQuoteCurrencyCode();
        }else{
			$data['Amount'] = number_format($quoteObj->getBaseGrandTotal(), 2, '.', '');
			$data['Currency'] = $quoteObj->getBaseCurrencyCode();
        }

		$data['Description'] = $this->cleanInput('product purchase', 'Text');

		$data['SuccessURL'] = Mage::getUrl('sgps/formPayment/success', array (
			'_secure' => true,
			'_nosid' => true,
			'vtxc' => $data['VendorTxCode'],
		));
		$data['FailureURL'] = Mage::getUrl('sgps/formPayment/failure', array (
			'_secure' => true,
			'_nosid' => true,
			'vtxc' => $data['VendorTxCode'],
		));

		$data['BillingSurname'] = $this->ss($billing->getLastname(), 20);
		$data['ReferrerID'] = $this->getConfigData('referrer_id');
		$data['BillingFirstnames'] = $this->ss($billing->getFirstname(),20);
		$data['BillingAddress1'] = ($this->getConfigData('mode') == 'test') ? 88 : $this->ss($billing->getStreet(1), 100);
		$data['BillingAddress2'] = ($this->getConfigData('mode') == 'test') ? 88 : $this->ss($billing->getStreet(2), 100);
		$data['BillingPostCode'] = ($this->getConfigData('mode') == 'test') ? 412 : preg_replace("/[^a-zA-Z0-9-\s]/", "", $this->ss($billing->getPostcode(), 10));
		$data['BillingCity'] = $this->ss($billing->getCity(), 40);
		$data['BillingCountry'] = $billing->getCountry();
		$data['BillingPhone'] = $this->_cphone($billing->getTelephone());

		// Set delivery information for virtual products ONLY orders
		if ($quoteObj->getIsVirtual()) {
			$data['DeliverySurname'] = $this->ss($billing->getLastname(),20);
			$data['DeliveryFirstnames'] = $this->ss($billing->getFirstname(),20);
			$data['DeliveryAddress1'] = $this->ss($billing->getStreet(1), 100);
			$data['DeliveryAddress2'] = $this->ss($billing->getStreet(2), 100);
			$data['DeliveryCity'] = $this->ss($billing->getCity(), 40);
			$data['DeliveryPostCode'] = preg_replace("/[^a-zA-Z0-9-\s]/", "", $this->ss($billing->getPostcode(), 10));
			$data['DeliveryCountry'] = $billing->getCountry();
			$data['DeliveryPhone'] = $this->_cphone($billing->getTelephone());
		} else {
			$data['DeliveryPhone'] = $this->_cphone($shipping->getTelephone());
			$data['DeliverySurname'] = $this->ss($shipping->getLastname(),20);
			$data['DeliveryFirstnames'] = $this->ss($shipping->getFirstname(),20);
			$data['DeliveryAddress1'] = $this->ss($shipping->getStreet(1), 100);
			$data['DeliveryAddress2'] = $this->ss($shipping->getStreet(2), 100);
			$data['DeliveryCity'] = $this->ss($shipping->getCity(), 40);
			$data['DeliveryPostCode'] = preg_replace("/[^a-zA-Z0-9-\s]/", "", $this->ss($shipping->getPostcode(), 10));
			$data['DeliveryCountry'] = $shipping->getCountry();
		}

		if ($data['DeliveryCountry'] == 'US') {
			if($quoteObj->getIsVirtual()){
				$data['DeliveryState'] = $billing->getRegionCode();
			}else{
				$data['DeliveryState'] = $shipping->getRegionCode();
			}

		}
		if ($data['BillingCountry'] == 'US') {
			$data['BillingState'] = $billing->getRegionCode();
		}

		$shippingTotal = isset ($totals['shipping']) ? $totals['shipping']->getvalue() : 0;

		if($this->getSendBasket()){
			$data['Basket'] = mb_convert_encoding($this->_getBasketContents($quoteObj), 'ISO-8859-1', 'UTF-8');
		}

		$data['AllowGiftAid'] = (int)$this->getConfigData('allow_gift_aid');
		$data['ApplyAVSCV2']  = $this->getConfigData('avscv2');
        $data['SendEMail']    = (int)$this->getConfigData('send_email');

        $vendorEmail = (string)$this->getConfigData('vendor_email');
        if($vendorEmail){
			$data['VendorEMail']  = $vendorEmail;
        }

		$dataToSend = '';
		foreach($data as $field => $value) {
			if($value!='') {
				$dataToSend .= ($dataToSend == '') ? "$field=$value" : "&$field=$value";
			}
		}

		Sage_Log::log($data, null, 'SagePaySuite_REQUEST.log');

		$trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
            ->loadByVendorTxCode($data['VendorTxCode'])
            ->setVendorTxCode($data['VendorTxCode'])
            ->setVpsProtocol($this->getVpsProtocolVersion())
            ->setVendorname($this->getConfigData('vendor'))
            ->setMode($this->getConfigData('mode'))
            ->setTxType(strtoupper($this->getConfigData('payment_action')))
            ->setTrnCurrency($data['Currency'])
            ->setIntegration('form')
            ->setTrndate($this->getDate())->save();

		$dataCrypt = base64_encode($this->simpleXor($dataToSend, $cryptPass));

		return $dataCrypt;
	}

    public function capture(Varien_Object $payment, $amount)
    {
        #Process invoice
        if(!$payment->getRealCapture()){
            return $this->captureInvoice($payment, $amount);
        }
    }

}