<?php

/**
 * Wrapper that performs Paypal Express and Checkout communication
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Model_Paypal_Checkout
{

	protected $_billingAddressMap = array (
        'CustomerEMail' => 'email',
        'DeliveryFirstnames' => 'firstname',
        'DeliverySurname' => 'lastname',
        'DeliveryCountry' => 'country_id', // iso-3166 two-character code
        'DeliveryState'    => 'region',
        'DeliveryCity'     => 'city',
        'DeliveryAddress1'   => 'street',
        'DeliveryPostCode'      => 'postcode'
    );

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    /**
     * Config instance
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * Customer ID
     *
     * @var int
     */
    protected $_customerId = null;

    /**
     * Recurring payment profiles
     *
     * @var array
     */
    protected $_recurringPaymentProfiles = array();

    /**
     * Billing agreement that might be created during order placing
     *
     * @var Mage_Sales_Model_Billing_Agreement
     */
    protected $_billingAgreement = null;

    /**
     * Order
     *
     * @var Mage_Sales_Model_QuoteMage_Sales_Model_Quote
     */
    protected $_order = null;

    /**
     * System information map
     *
     * @var array
     */
    protected $_systemMap = array(
        'payment_status' => 'paypal_payment_status',
        'pending_reason' => 'paypal_pending_reason',
        'is_fraud_detected'       => 'paypal_is_fraud_detected',
    );

    /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMap = array(
        'payer_id'       => 'paypal_payer_id',
        'email'    => 'paypal_payer_email',
        'payer_status'   => 'paypal_payer_status',
        'address_id'     => 'paypal_address_id',
        'address_status' => 'paypal_address_status',
        'protection_eligibility'  => 'paypal_protection_eligibility',
        'collected_fraud_filters' => 'paypal_fraud_filters',
        'correlation_id' => 'paypal_correlation_id',
        'avs_result'       => 'paypal_avs_code',
        'cvv2_check_result'     => 'paypal_cvv2_match',
    );

    /**
     * Set quote and config instances
     * @param array $params
     */
    public function __construct($params = array())
    {
        if (isset($params['quote']) && $params['quote'] instanceof Mage_Sales_Model_Quote) {
            $this->_quote = $params['quote'];
        } else {
            throw new Exception('Quote instance is required.');
        }
    }

    /**
     * Setter for customer Id
     *
     * @param int $id
     * @return Mage_Paypal_Model_Express_Checkout
     * @deprecated please use self::setCustomer
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }

    /**
     * Setter for customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Paypal_Model_Express_Checkout
     */
    public function setCustomer($customer)
    {
        $this->_quote->assignCustomer($customer);
        $this->_customerId = $customer->getId();
        return $this;
    }

    /**
     * Reserve order ID for specified quote and start checkout on PayPal
     * @return string
     */
    public function start()
    {

        $this->_quote->collectTotals();
        $this->_quote->reserveOrderId()->save();

        // supress or export shipping address
            $this->_quote->getPayment()->save();

        // call API and redirect with token
			$rs = Mage::getModel('sagepaysuite/sagePayDirectPro')->registerPayPalTransaction();

			if($rs->getPayPalRedirectUrl()){
				return $rs->getPayPalRedirectUrl();
			}else{
				return $rs;
			}

    }

    /**
     * Update quote when returned from PayPal
     * @param string $token
     */
    public function returnFromPaypal(Mage_Core_Controller_Request_Http $paypalData)
    {

        // import billing address (only if not already set in quote (incheckout paypal))
		$billingAddress = $this->_quote->getBillingAddress();
        $baddressValidation = $billingAddress->validate();
        if ($baddressValidation !== true) {
			foreach ($this->_billingAddressMap as $key=>$value) {

				$arvalue = $paypalData->getPost($key);
				if( $value == 'street' ){
					$arvalue = $paypalData->getPost('DeliveryAddress1') ."\n". $paypalData->getPost('DeliveryAddress2');
				}

            	$billingAddress->setDataUsingMethod($value, mb_convert_encoding($arvalue, 'UTF-8', 'ISO-8859-15'));
        	}

        }

        // import shipping address
        if (!$this->_quote->getIsVirtual() && ($paypalData->getPost('AddressStatus') != 'NONE') ) {
            $shippingAddress = $this->_quote->getShippingAddress();
            if ($shippingAddress) {
                    foreach ($this->_billingAddressMap as $key=>$value) {

						$arvalue = $paypalData->getPost($key);
						if( $value == 'street' ){
							$arvalue = $paypalData->getPost('DeliveryAddress1') ."\n". $paypalData->getPost('DeliveryAddress2');
						}

                        $shippingAddress->setDataUsingMethod($value, mb_convert_encoding($arvalue, 'UTF-8', 'ISO-8859-15'));
                    }
                    $this->_quote->getShippingAddress()->setCollectShippingRates(true);
            }
        }
        $this->_ignoreAddressValidation();

        // import payment info
        $payment = $this->_quote->getPayment();
        $payment->setMethod('sagepaydirectpro');

        $this->_quote->collectTotals()->save();
    }

    /**
     * Grab data from source and map it into payment
     *
     * @param array|Varien_Object|callback $from
     * @param Mage_Payment_Model_Info $payment
     */
    public function importToPayment($from, Mage_Payment_Model_Info $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = array($from, 'getDataUsingMethod');
        }
        Varien_Object_Mapper::accumulateByMap($from, array($payment, 'setAdditionalInformation'), $fullMap);
    }

    /**
     * Check whether order review has enough data to initialize
     *
     * @param $token
     * @throws Mage_Core_Exception
     */
    public function prepareOrderReview()
    {
        $payment = $this->_quote->getPayment();

        if (!$payment || (!$this->_quote->getCustomerEmail())) {
            Mage::throwException(Mage::helper('sagepaysuite')->__('Payer is not identified.'));
        }
        $this->_ignoreAddressValidation();
        $this->_quote->collectTotals()->save();
    }

    /**
     * Return callback response with shipping options
     *
     * @param array $request
     * @return string
     */
    public function getShippingOptionsCallbackResponse(array $request)
    {
        // prepare debug data
        $logger = Mage::getModel('core/log_adapter', 'payment_' . $this->_methodType . '.log');
        $debugData = array('request' => $request, 'response' => array());

        try {
            // obtain addresses
            $this->_getApi();
            $address = $this->_api->prepareShippingOptionsCallbackAddress($request);
            $quoteAddress = $this->_quote->getShippingAddress();

            // compare addresses, calculate shipping rates and prepare response
            $options = array();
            if ($address && $quoteAddress && !$this->_quote->getIsVirtual()) {
                foreach ($address->getExportedKeys() as $key) {
                    $quoteAddress->setDataUsingMethod($key, $address->getData($key));
                }
                $quoteAddress->setCollectShippingRates(true)->collectShippingRates();
                $options = $this->_prepareShippingOptions($quoteAddress, false);
            }
            $response = $this->_api->setShippingOptions($options)->formatShippingOptionsCallback();

            // log request and response
            $debugData['response'] = $response;
            $logger->log($debugData);
            return $response;
        } catch (Exception $e) {
            $logger->log($debugData);
            throw $e;
        }
    }

    /**
     * Set shipping method to quote, if needed
     * @param string $methodCode
     */
    public function updateShippingMethod($methodCode)
    {
        if (!$this->_quote->getIsVirtual() && $shippingAddress = $this->_quote->getShippingAddress()) {
            if ($methodCode != $shippingAddress->getShippingMethod()) {
                $this->_ignoreAddressValidation();
                $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);
                $this->_quote->collectTotals()->save();
            }
        }
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareNewCustomerQuote()
    {
        $quote      = $this->_quote;
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        Mage::helper('core')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $quote->setCustomer($customer)
            ->setCustomerId(true);
    }

    /**
     * Involve new customer to system
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _involveNewCustomer()
    {
        $customer = $this->_quote->getCustomer();
        if ($customer->isConfirmationRequired()) {
            $customer->sendNewAccountEmail('confirmation');
            $url = Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail());
            Mage::getSingleton('checkout/type_onepage')->getCustomerSession()->addSuccess(
                Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.', $url)
            );
        } else {
            $customer->sendNewAccountEmail();
            Mage::getSingleton('checkout/type_onepage')->getCustomerSession()->loginById($customer->getId());
        }
        return $this;
    }

    /**
     * Place the order and recurring payment profiles when customer returned from paypal
     * Until this moment all quote data must be valid
     */
    public function place()
    {
		$isNewCustomer = false;
        if( $this->_quote->getCheckoutMethod() == 'register' ){

			$this->_prepareNewCustomerQuote();
			$isNewCustomer = true;

        }elseif ( !$this->_quote->getCustomerId() ) {
            $this->_quote->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
                ->setCustomerEmail($this->_quote->getBillingAddress()->getEmail());
        }
		elseif($this->_quote->getCustomerId()) {
			if (! $this->_quote->getBillingAddress()->getCustomerAddressId()) {
				$billingAddress = Mage::getModel('customer/address');
				$billingAddress->setData($this->_quote->getBillingAddress()->getData())
							->setCustomerId($this->_quote->getCustomerId())
							->setSaveInAddressBook('1')
							->setIsDefaultBilling('1');

				if ($this->_quote->getShippingAddress()->getData('same_as_billing')) {
					$billingAddress->setIsDefaultShipping('1');
				}
				else {
					$shippingAddress = Mage::getModel('customer/address');
					$shippingAddress->setData($this->_quote->getShippingAddress()->getData())
								->setCustomerId($this->_quote->getCustomerId())
								->setSaveInAddressBook('1')
								->setIsDefaultShipping('1');

					$shippingAddress->save();
				}
				$billingAddress->save();
			}
			else {
				if ($this->_quote->getBillingAddress()->getSaveInAddressBook()) {

					$newAddress = Mage::getModel('customer/address');
					$newAddress->setData($this->_quote->getBillingAddress()->getData())
							->setCustomerId($this->_quote->getCustomerId())
							->setSaveInAddressBook('1')
							->save();
				}
				if ($this->_quote->getShippingAddress()->getSaveInAddressBook() && !$this->_quote->getShippingAddress()->getData('same_as_billing')) {

					$newAddress = Mage::getModel('customer/address');
					$newAddress->setData($this->_quote->getShippingAddress()->getData())
							->setCustomerId($this->_quote->getCustomerId())
							->setSaveInAddressBook('1')
							->save();
				}
			}
		}

        $this->_ignoreAddressValidation();
        $this->_quote->collectTotals();

        // commence redirecting to finish payment
		$rs = Mage::getModel('sagepaysuite/sagePayDirectPro')->completePayPalTransaction(Mage::getSingleton('sagepaysuite/session')->getSagepaypaypalRqpost(), $this->_quote);

        $service = Mage::getModel('sales/service_quote', $this->_quote);
        $service->submitAll();
        $this->_quote->save();

        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $order = $service->getOrder();
        if (!$order) {
            return;
        }

        switch ($order->getState()) {
            // even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                // TODO
                break;
            // regular placement, when everything is ok
            case Mage_Sales_Model_Order::STATE_PROCESSING:
            case Mage_Sales_Model_Order::STATE_COMPLETE:
            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
                $order->sendNewOrderEmail();
                break;
        }
        $this->_order = $order;
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
     * Return recurring payment profiles
     *
     * @return array
     */
    public function getRecurringPaymentProfiles()
    {
        return $this->_recurringPaymentProfiles;
    }

    /**
     * Get created billing agreement
     *
     * @return Mage_Sales_Model_Billing_Agreement|null
     */
    public function getBillingAgreement()
    {
        return $this->_billingAgreement;
    }

    /**
     * Return order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return Mage_Paypal_Model_Api_Nvp
     */
    protected function _getApi()
    {
        if (null === $this->_api) {
            $this->_api = Mage::getModel($this->_apiType)->setConfigObject($this->_config);
        }
        return $this->_api;
    }

    /**
     * Attempt to collect address shipping rates and return them for further usage in instant update API
     * Returns empty array if it was impossible to obtain any shipping rate
     * If there are shipping rates obtained, the method must return one of them as default.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param bool $mayReturnEmpty
     * @return array|false
     */
    protected function _prepareShippingOptions(Mage_Sales_Model_Quote_Address $address, $mayReturnEmpty = false)
    {
        $options = array(); $i = 0; $iMin = false; $min = false; $iDefault = false;

        foreach ($address->getGroupedAllShippingRates() as $group) {
            foreach ($group as $rate) {
                $amount = (float)$rate->getPrice();
                if (!$rate->getMethodTitle() || 0.00 == $amount) {
                    continue;
                }
                $isDefault = $address->getShippingMethod() === $rate->getCode();
                if ($isDefault) {
                    $iDefault = $i;
                }
                $options[$i] = new Varien_Object(array(
                    'is_default' => $isDefault,
                    'name'       => "{$rate->getCarrierTitle()} - {$rate->getMethodTitle()}",
                    'code'       => $rate->getCode(),
                    'amount'     => $amount,
                ));
                if (false === $min || $amount < $min) {
                    $min = $amount; $iMin = $i;
                }
                $i++;
            }
        }

        if ($mayReturnEmpty) {
            $options[] = new Varien_Object(array(
                'is_default' => (false === $iDefault ? true : false),
                'name'       => 'N/A',
                'code'       => 'no_rate',
                'amount'     => 0.00,
            ));
        } elseif (false === $iDefault && isset($options[$iMin])) {
            $options[$iMin]->setIsDefault(true);
        }

        return $options;
    }

    /**
     * Try to find whether the code provided by PayPal corresponds to any of possible shipping rates
     * This method was created only because PayPal has issues with returning the selected code.
     * If in future the issue is fixed, we don't need to attempt to match it. It would be enough to set the method code
     * before collecting shipping rates
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $selectedCode
     * @return string
     */
    protected function _matchShippingMethodCode(Mage_Sales_Model_Quote_Address $address, $selectedCode)
    {
        $options = $this->_prepareShippingOptions($address, false);
        foreach ($options as $option) {
            if ($selectedCode === $option['code'] // the proper case as outlined in documentation
                || $selectedCode === $option['name'] // workaround: PayPal may return name instead of the code
                // workaround: PayPal may concatenate code and name, and return it instead of the code:
                || $selectedCode === "{$option['code']} {$option['name']}"
            ) {
                return $option['code'];
            }
        }
        return '';
    }
}
