<?php
class Idev_OneStepCheckout_Model_Observers_PresetDefaults extends Mage_Core_Model_Abstract {

    //@TODO together with refactoring system.xml: get rid of this variable and add them as config nodes
    public $defaultFields = array('country_id', 'region', 'region_id', 'city', 'postcode');

    /**
     * shipping rates array
     * @var array
     */
    protected $_rates = array();

    /**
     * ShippingMethod block class
     * @var Mage_Checkout_Block_Onepage_Payment_Methods
     */
    protected $_paymentMethodsBlock = null;

    /**
     * payment methods array
     * @var array
     */
    protected $_methods = array();

    /**
     * Call default set methods wrapper
     *
     * @param Varien_Event_Observer $observer
     * @return Idev_OneStepCheckout_Model_Observers_PresetDefaults
     */
    public function setDefaults(Varien_Event_Observer $observer) {

        $quote = $observer->getEvent()->getQuote();

        if(Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links', $quote->getStore())) {
            $this->callDefaults($observer);

        }

        return $this;
    }

    /**
     * Call defaults method
     *
     * @param Varien_Event_Observer $observer
     */
    public function callDefaults (Varien_Event_Observer $observer) {

        $this->setAddressDefaults($observer);
        $this->setShippingDefaults($observer);
        $this->setPaymentDefaults($observer);

        return $this;
    }


    /**
     * If customer logs in and there are default data that is different from entered data we need to reset defaults
     *
     * @param Varien_Event_Observer $observer
     */
    public function setDefaultsOnLogin(Varien_Event_Observer $observer) {

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if(is_object($quote)){
            $currentBilling = $this->hasDataSet($quote->getBillingAddress());
            $currentPrimaryBilling = $this->hasDataSet($quote->getCustomer()->getPrimaryBillingAddress());
            $difference  = array_diff($currentPrimaryBilling, $currentBilling);
            if(!empty($currentBilling) && !empty($difference)){
                foreach($this->defaultFields as $field){
                    $quote->getBillingAddress()->setData($field, '');
                    $quote->getShippingAddress()->setData($field, '');
                }
            }
            $observer->getEvent()->setQuote($quote);
            if(Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links')) {
                $this->callDefaults($observer);
            }
        }

        return $this;
    }

    /**
     * If you have aquired a quote from cart and you are having saved addresses then you can get wrong shipping methods
     *
     * @param Varien_Event_Observer $observer
     */
    public function compareDefaultsFromCart(Varien_Event_Observer $observer) {

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if(is_object($quote)){

            //extract the data from quote
            $currentBilling = $this->hasDataSet($quote->getBillingAddress());
            $currentShipping = $this->hasDataSet($quote->getShippingAddress());

            $sameAsBilling = $quote->getShippingAddress()->getSameAsBilling();
            $difference = array();

            if($sameAsBilling){
                if(Mage::getSingleton('customer/session')->isLoggedIn()){
                    $selectedAddress = $quote->getBillingAddress()->getCustomerAddressId();
                    if($selectedAddress){
                        $currentShippingOriginal = $this->hasDataSet($quote->getCustomer()->getAddressById($selectedAddress));
                        $difference = array_diff($currentShippingOriginal, $currentShipping);
                    } else {
                        $currentPrimaryBilling = $this->hasDataSet($quote->getCustomer()->getPrimaryBillingAddress());
                        $difference  = array_diff($currentPrimaryBilling, $currentBilling);
                    }
                } else {
                    $difference = array_diff($currentBilling, $currentShipping);
                }

                if(!empty($difference)){
                    $quote->getBillingAddress()->addData($difference)->implodeStreetAddress();
                    $quote->getShippingAddress()->addData($difference)->implodeStreetAddress()->setCollectShippingRates(true);
                }
            }

        }

        return $this;
    }

    /**
     * Callback to see if shipping rates have changed after totals are set to quote.
     *
     * @param Varien_Event_Observer $observer
     * @return Idev_OneStepCheckout_Model_Observers_PresetDefaults
     */
    public function setShippingIfDifferent(Varien_Event_Observer $observer){

        $quote = $observer->getEvent()->getQuote();

        if(!Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links', $quote->getStore())) {
            return $this;
        }

        $newCode = Mage::getStoreConfig('onestepcheckout/general/default_shipping_method', $quote->getStore());

        if (empty($newCode)) {
            return $this;
        }

        //request rate calculation
        $quote->getShippingAddress()->collectShippingRates();

        return $this;
    }

    /**
     * Sets the default shipping/billing data to pass validations and reveal data
     *
     * @param Varien_Event_Observer $observer
     * @return Idev_OneStepCheckout_Model_Observers_PresetDefaults
     */
    public function setAddressDefaults(Varien_Event_Observer $observer) {

        $quote = $observer->getEvent()->getQuote();

        $checkPostcode = $this->getCheckPostcode();
        $currentBilling = $this->hasDataSet($quote->getBillingAddress(), $checkPostcode);
        $currentShipping = $this->hasDataSet($quote->getShippingAddress(), $checkPostcode);

        if (!is_object($quote) || (!empty($currentBilling) || !empty($currentShipping))) {
            return $this; // data already set
        }

        $newShipping = $this->getAddressDefaults($quote);
        $newBilling = $newShipping;

        if (empty($newShipping)) {
            return $this; // no data as default means nothing is to set
        }

        //if user is logged in and no data is set else we use defaults
        if (Mage::getSingleton('customer/session')->isLoggedIn() && empty($currentBilling)) {

            //we look for default addresses and extract the data from there
            $currentPrimaryBilling = $this->hasDataSet($quote->getCustomer()->getPrimaryBillingAddress(), $checkPostcode);
            $currentPrimaryShipping = $this->hasDataSet($quote->getCustomer()->getPrimaryShippingAddress(), $checkPostcode);

            //and if we have data we set it to default
            if (empty($currentBilling) && ! empty($currentPrimaryBilling)) {
                $newBilling = $currentPrimaryBilling;
            }
            if (empty($currentShipping) && ! empty($currentPrimaryShipping)) {
                $newShipping = $currentPrimaryShipping;
            }
        }

        //if shipping should be same as billing
        if ($quote->getShippingAddress()->getSameAsBilling()) {
            $newShipping = $newBilling;
        }

        //only add if there is nothing here
        if (empty($currentBilling) && ! empty($newBilling)) {
            $quote->getBillingAddress()->addData($newBilling);
        }

        //only add if there is nothing here
        if (empty($currentShipping) && ! empty($newShipping)) {
            $quote->getShippingAddress()->addData($newShipping);
            $quote->getShippingAddress()->setSameAsBilling(Mage::getStoreConfig('onestepcheckout/general/enable_different_shipping_hide', $quote->getStore()));
        }

        return $this;
    }

    /**
     * Check if postcode is required for ajax update and should be validated
     *
     * @return boolean
     */
    public function getCheckPostcode (){
        return ((strstr(Mage::getStoreConfig('onestepcheckout/ajax_update/ajax_save_billing_fields'),'postcode')) ? true : false);
    }

    /**
     * Get default config values for address
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getAddressDefaults(Mage_Sales_Model_Quote $quote){

        $data = $this->getAddressGeoIP($quote);

        if(!empty($data)){
            return $data;
        }

        if($countryId = Mage::getStoreConfig('onestepcheckout/general/default_country',$quote->getStore())){
            $data['country_id'] = $countryId;
        }
        if($regionId = Mage::getStoreConfig('onestepcheckout/general/default_region_id',$quote->getStore())){
            $data['region_id'] = $regionId;
        }
        if($city = Mage::getStoreConfig('onestepcheckout/general/default_city',$quote->getStore())){
            $data['city'] = $city;
        }
        if($postcode = Mage::getStoreConfig('onestepcheckout/general/default_postcode',$quote->getStore())){
            $data['postcode'] = $postcode;
        }

        return $data;
    }

    /**
     * Get GeoIp data by user ip address
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getAddressGeoIP(Mage_Sales_Model_Quote $quote){

        $data = array();
        $ipaddress = $_SERVER['REMOTE_ADDR'];
        $geoip = false;

        $enabled = Mage::getStoreConfig('onestepcheckout/general/enable_geoip',$quote->getStore());
        $database = Mage::getBaseDir('base') . DS . Mage::getStoreConfig('onestepcheckout/general/geoip_database',$quote->getStore());

        if(!$enabled || !file_exists($database) || !$ipaddress ){
            return $data;
        }

        try {
            if (!@include_once('Net/GeoIP.php')) {
                Mage::throwException(Mage::helper('onestepcheckout')->__('Net/GeoIP pear package is not installed or inaccessible'));
            } else {
                require_once('Net/GeoIP.php');
                $geoip = Net_GeoIP::getInstance($database);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            // city database
            if(is_object($geoip) && method_exists($geoip,'lookupLocation')){
                $location = $geoip->lookupLocation($ipaddress);
                $data['country_id'] = $location->countryCode;
                $data['region_id'] = Mage::getModel('directory/region')->loadByCode($location->region,$location->countryCode)->getRegionId();
                $data['city'] = ($location->city) ? utf8_encode($location->city) : '';
                $data['postcode'] = ($location->postalCode) ? $location->postalCode : '';
            // country database
            } else if(is_object($geoip) && method_exists($geoip,'lookupCountryCode')){
                $data['country_id'] = $geoip->lookupCountryCode($ipaddress);
            //no database
            } else {
                Mage::throwException(Mage::helper('onestepcheckout')->__('Net/GeoIP database %s is not installed properly or is inaccessible', $database));
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $data;
    }

    /**
     * Select and set default shipping method from available methods
     *
     * @param Varien_Event_Observer $observer
     * @return Idev_OneStepCheckout_Model_Observers_PresetDefaults
     */
    public function setShippingDefaults(Varien_Event_Observer $observer) {

        $quote = $observer->getEvent()->getQuote();
        $newCode = Mage::getStoreConfig('onestepcheckout/general/default_shipping_method', $quote->getStore());
        $oldCode = $quote->getShippingAddress()->getShippingMethod();
        $codes = array();

        if (empty($newCode)) {
            return $this;
        }

        foreach ($this->getEstimateRates($quote) as $rates) {
            foreach ($rates as $rate) {
                $codes[] = $rate->getCode();
            }
        }

        if (empty($codes)) {
            return $this;
        }

        $codeCount = (int)count($codes);

        //if we have only one rate available select it no matter what the default is
        if ($codeCount === 1) {
            if(Mage::getStoreConfig('onestepcheckout/general/default_shipping_if_one', $quote->getStore())){
                $newCode = current($codes);
            }
        }

        if (! empty($codes) && (empty($oldCode) || ! in_array($oldCode, $codes))) {
            if (in_array($newCode, $codes)) {
                $quote->getShippingAddress()->setShippingMethod($newCode);
            }
        }

        return $this;
    }

    /**
     * get all shipping rates
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getEstimateRates(Mage_Sales_Model_Quote $quote) {
        if (empty($this->_rates)) {
            $groups = $quote->getShippingAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }
        return $this->_rates;
    }

    /**
     * Set default payment method for the user
     *
     * @param Varien_Event_Observer $observer
     * @return Idev_OneStepCheckout_Model_Observers_PresetDefaults
     */
    public function setPaymentDefaults(Varien_Event_Observer $observer) {

        $quote = $observer->getEvent()->getQuote();
        $newCode = Mage::getStoreConfig('onestepcheckout/general/default_payment_method', $quote->getStore());

        if (empty($newCode)) {
            return $this;
        }

        $oldCode = $quote->getPayment()->getMethod();
        $codes = $this->getPaymentMethods($quote);

        if (empty($codes) || !is_object($quote) || !$quote->getGrandTotal()) {
            return $this;
        }

        $codeCount = (int)count($codes);

        //if we have only one rate available select it no matter what the default is
        if ($codeCount === 1) {
            $newCode = current($codes);
        }

        if (!empty($codes) && (empty($oldCode) || !in_array($oldCode, $codes))) {
            if (in_array($newCode, $codes)) {

                //only if method is actually active we can set this as default
                if(Mage::getStoreConfig('payment/'.$newCode.'/active', $quote->getStore())){
                    if ($quote->isVirtual()) {
                        $quote->getBillingAddress()->setPaymentMethod($newCode);
                    } else {
                        $quote->getShippingAddress()->setPaymentMethod($newCode);
                    }
                    try {
                        $quote->getPayment()->setMethod($newCode)->getMethodInstance();
                    } catch ( Exception $e ) {
                        Mage::logException($e);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve availale payment methods
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getPaymentMethods(Mage_Sales_Model_Quote $quote) {

        $methods = $this->_methods;
        if (empty($methods)) {
            $store = $quote ? $quote->getStoreId() : null;
            $methodInstances = Mage::helper('payment')->getStoreMethods($store, $quote);
            $total = $quote->getGrandTotal();
            foreach ($methodInstances as $key => $method) {
                if ($this->_canUseMethod($method, $quote)
                        && ($total != 0
                                || $method->getCode() == 'free'
                                || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                    $methods[] = $method->getCode();
                } else {
                    unset($methods[$key]);
                }
            }

            $this->_methods = $methods;
        }
        return $this->_methods;
    }

    /**
     * Check if method can be used
     *
     * @param string $method
     * @param object $quote
     * @return boolean
     */
    protected function _canUseMethod($method, $quote)
    {
        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (method_exists($method,'canUseForCurrency') && !$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }

    /**
     * Check if object has values or default values set
     *
     * @param Mage_Sales_Model_Quote_Address $addressObject
     * @return array();
     */
    public function hasDataSet($address, $checkPostcode = false){

        $data = array();

        if(is_object($address)){
            foreach($address->getData() as $key => $value){
                if(in_array($key, $this->defaultFields) && !empty($value)){
                    $data[$key] = $value;
                }
            }
        }

        if($checkPostcode && empty($data['postcode'])){
            $data = array();
        }
        return $data;
    }

}
