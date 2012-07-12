<?php
/**
 *  OneStepCheckout main block
 *  @author Jone Eide <mail@onestepcheckout.com>
 *  @copyright Jone Eide <mail@onestepcheckout.com>
 *
 */
class Idev_OneStepCheckout_Block_Checkout extends Mage_Checkout_Block_Onepage_Abstract  {

    public $formErrors;
    public $settings;
    public $log = array();
    public $_rates = 0;

    const SESSION_ADDRESS_CHECK_NAME = 'onestepcheckout_address_check_name';

    protected function _loadConfig()
    {
        $this->settings = Mage::helper('onestepcheckout/checkout')->loadConfig();
    }

    public function _getDefaultShippingMethod()
    {
        if($this->settings['default_shipping_method'] != '')    {
            return $this->settings['default_shipping_method'];
        }
        else    {
            $check_single = $this->_checkSingleShippingMethod();
            if($check_single)   {
                return $check_single;
            }
        }
    }

    protected function _checkSingleShippingMethod()
    {
        $rates = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRatesCollection();
        $rateCodes = array();

        foreach($rates as $rate)    {
            if(!in_array($rate->getCode(), $rateCodes)) {
                $rateCodes[] = $rate->getCode();
            }
        }

        if(count($rateCodes) == 1)  {
            return $rateCodes[0];
        }

        return false;
    }

    protected function _isLoggedInWithAddresses()
    {
        $helper = $this->helper('customer');
        if( $helper->isLoggedIn() && $helper->customerHasAddresses() )  {
            return true;
        }

        return false;
    }

    protected function _isLoggedIn()
    {
        $helper = $this->helper('customer');
        if( $helper->isLoggedIn() ) {
            return true;
        }

        return false;

    }

    public function _construct()
    {
        parent::_construct();

        $this->getQuote()->setIsMultiShipping(false);

        $this->email = false;
        $this->customer_after_place_order = false;

        $this->_loadConfig();

        if($this->_isLoggedIn())    {
            $helper = Mage::helper('customer');
            $customer = $helper->getCustomer();
            $this->email = $customer->getEmail();
        }

        //we need to refactor this , not a neat way to make all in constructor
        if($this->getSubTemplate()){
            return true;
        }

        try {
            $this->_handlePostData();
        } catch(Exception $e)   {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getEstimateRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }
        return $this->_rates;
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }

            $addressId = '';
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getDefaultBillingAddress();
                } else {
                    $address = $this->getCustomer()->getDefaultShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            if ($type=='billing') {
                $address = $this->getQuote()->getBillingAddress();
            } else {
                $address = $this->getQuote()->getShippingAddress();
            }
            if ($address) {
                    $addressIde = $address->getCustomerAddressId();
                    if($addressIde){
                        $addressId = $addressIde;
                    }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            $isPost = $this->getRequest()->getPost();
            $isPost = (!empty($isPost));
            $selectedValue = $this->getRequest()->getPost('billing_address_id', false);


            if($this->getNewAddressSelectValueOnError($type)){
                 $select->setValue('');
            }

            return $select->getHtml();
        }
        return '';
    }

    public function getNewAddressSelectValueOnError($type){

        if ($type=='billing') {
            $selectedValue = $this->getRequest()->getPost('billing_address_id', false);
        } else {
            $selectedValue = $this->getRequest()->getPost('shipping_address_id', false);
        }
        $isPost = $this->getRequest()->getPost();
        $isPost = (!empty($isPost));

        if($isPost && $selectedValue == ''){
            return true;
        }

        return false;
    }

    public function hasAjaxSaveBillingField($name)
    {
        $fields = explode(',', $this->settings['ajax_save_billing_fields']);

        if(in_array($name, $fields))    {
            return true;
        }

        return false;
    }

    public function sameAsBilling()
    {
        $return = true;

        if($_SERVER['REQUEST_METHOD'] == 'POST')    {
            if(empty($_POST['billing']['use_for_shipping']))   {
                $return = false;
            } else {
                $return = true;
            }
        }

        $address = $this->getQuote()->getShippingAddress();

        if(!$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            $return = false;
        } else {
            $return = true;
        }

        return $return;
    }

    public function differentShippingAvailable()
    {
        if($this->isVirtual())  {
            return false;
        }

        if($this->settings['enable_different_shipping'])    {
            return true;
        }

        return false;
    }

    public function isVirtual()
    {
        return $this->getOnepage()->getQuote()->isVirtual();
    }

    public function hasFormErrors()
    {
        if($this->hasShippingErrors() || $this->hasBillingErrors() || $this->hasMethodErrors() || $this->hasShipmentErrors()) {
            return true;
        }

        return false;
    }

    public function hasMethodErrors()
    {
        if(isset($this->formErrors['shipping_method']) && $this->formErrors['shipping_method']) {
            return true;
        }

        if(isset($this->formErrors['payment_method']) && $this->formErrors['payment_method'])   {
            return true;
        }

        if(isset($this->formErrors['payment_method_error']))    {
            return true;
        }

        if(isset($this->formErrors['terms_error'])) {
            return true;
        }

        if(isset($this->formErrors['agreements_error'])) {
            return true;
        }

        return false;
    }

    public function hasShippingErrors()
    {
        if(isset($this->formErrors['shipping_errors']))  {
            if(count($this->formErrors['shipping_errors']) == 0) {
                return false;
            }
            return true;
        }
        else    {
            return true;
        }
    }

    public function hasBillingErrors()
    {
        if(count($this->formErrors) > 0)   {
            if(isset($this->formErrors['billing_errors']))  {
                if(count($this->formErrors['billing_errors']) == 0) {

                    return false;
                }
                return true;
            }
            else    {
                return true;
            }
        }
        return false;
    }

    public function hasShipmentErrors()
    {
        if(!empty($this->formErrors['shipping_method'])){
            return true;
        }
        return false;
    }

    public function getAvailableRates($rates){
        $return = array();
        if(!empty($rates)){
            foreach ($rates as $_code => $_rates){
                foreach ($_rates as  $rate){
                    $return[] = $rate->getCode();
                }
            }
        }
        return $return;
    }

    public function _handlePostData()
    {
        $this->formErrors = array(
            'billing_errors' => array(),
            'shipping_errors' => array(),
        );

        $post = $this->getRequest()->getPost();




        if(!$post) return;

        // Save billing information
        if( $this->_isLoggedInWithAddresses() && false )    {

            // User is logged in and has addresses

        }
        else    {

            $checkoutHelper = Mage::helper('onestepcheckout/checkout');

            $payment_data = $this->getRequest()->getPost('payment');


            $billing_data = $this->getRequest()->getPost('billing', array());
            $shipping_data = $this->getRequest()->getPost('shipping', array());

            $billing_data = $checkoutHelper->load_exclude_data($billing_data);
            $shipping_data = $checkoutHelper->load_exclude_data($shipping_data);

            if(!empty($billing_data)){
                $this->getQuote()->getBillingAddress()->addData($billing_data)->implodeStreetAddress();
            }

            if($this->differentShippingAvailable()) {
                $this->getQuote()->getShippingAddress()->setCountryId($shipping_data['country_id'])->setCollectShippingRates(true);
            }

            if(isset($billing_data['email']))   {
                $this->email = $billing_data['email'];
            }

            if(!$this->_isLoggedIn()){
                $registration_mode = $this->settings['registration_mode'];
                if($registration_mode == 'auto_generate_account')   {
                    // Modify billing data to contain password also
                    $password = Mage::helper('onestepcheckout/checkout')->generatePassword();
                    $billing_data['customer_password'] = $password;
                    $billing_data['confirm_password'] = $password;
                    $this->getQuote()->getCustomer()->setData('password', $password);
                    $this->getQuote()->setData('password_hash',Mage::getModel('customer/customer')->encryptPassword($password));

                }


                if($registration_mode == 'require_registration' || $registration_mode == 'allow_guest')   {
                    if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']) && ($billing_data['customer_password'] == $billing_data['confirm_password'])){
                        $password = $billing_data['customer_password'];
                        $this->getQuote()->setCheckoutMethod('register');
                        $this->getQuote()->getCustomer()->setData('password', $password);
                        $this->getQuote()->setData('password_hash',Mage::getModel('customer/customer')->encryptPassword($password));
                    }
                }

            }

            if($this->_isLoggedIn() || $registration_mode == 'require_registration' || $registration_mode == 'auto_generate_account' || (!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']))){
                //handle this as Magento handles subscriptions for registered users (no confirmation ever)
                $subscribe_newsletter = $this->getRequest()->getPost('subscribe_newsletter');
                if(!empty($subscribe_newsletter)){
                    $this->getQuote()->getCustomer()->setIsSubscribed(1);
                }
            }

            $billingAddressId = $this->getRequest()->getPost('billing_address_id');
            $customerAddressId = (!empty($billingAddressId)) ? $billingAddressId : false ;

            if($this->_isLoggedIn()){
                $this->getQuote()->getBillingAddress()->setSaveInAddressBook(empty($billing_data['save_in_address_book']) ? 0 : 1);
                $this->getQuote()->getShippingAddress()->setSaveInAddressBook(empty($shipping_data['save_in_address_book']) ? 0 : 1);
            }

            $result = $this->getOnepage()->saveBilling($billing_data, $customerAddressId);

            if(!empty($billing_data['customer_password']) && !empty($billing_data['confirm_password']))   {
                // Trick to allow saving of
                $this->getOnepage()->saveCheckoutMethod('register');
                $customerData= array_intersect($billing_data, $this->getQuote()->getBillingAddress()->getData());
                $this->getQuote()->getCustomer()->addData($customerData);
                foreach($customerData as $key => $value){
                    $this->getQuote()->setData('customer_'.$key, $value);
                }
            }

            $customerSession = Mage::getSingleton('customer/session');

            if (!empty($billing_data['dob']) && !$customerSession->isLoggedIn()) {
                $dob = Mage::app()->getLocale()->date($billing_data['dob'], null, null, false)->toString('yyyy-MM-dd');
                $this->getQuote()->setCustomerDob($dob);
                $this->getQuote()->setDob($dob);
                $this->getQuote()->getBillingAddress()->setDob($dob);
            }

            if($customerSession->isLoggedIn() && !empty($billing_data['dob'])){
                $dob = Mage::app()->getLocale()->date($billing_data['dob'], null, null, false)->toString('yyyy-MM-dd');
                $customer = Mage::getModel('customer/customer')
                ->setId($customerSession->getId())
                ->setWebsiteId($customerSession->getCustomer()->getWebsiteId())
                ->setEmail($customerSession->getCustomer()->getEmail())
                ->setDob($dob)
                ->save()
                ;
            }

            if(!Mage::getStoreConfig('onestepcheckout/exclude_fields/enable_pillwaxeuvat_support')){
                // set customer tax/vat number for further usage
                if (!empty($billing_data['taxvat'])) {
                    $this->getQuote()->setCustomerTaxvat($billing_data['taxvat']);
                    $this->getQuote()->setTaxvat($billing_data['taxvat']);
                    $this->getQuote()->getBillingAddress()->setTaxvat($billing_data['taxvat']);
                }

                if($customerSession->isLoggedIn() && !empty($billing_data['taxvat'])){
                    $customer = Mage::getModel('customer/customer')
                    ->setId($customerSession->getId())
                    ->setWebsiteId($customerSession->getCustomer()->getWebsiteId())
                    ->setEmail($customerSession->getCustomer()->getEmail())
                    ->setTaxvat($billing_data['taxvat'])
                    ->save()
                    ;
                }
            }

            if(isset($result['error'])) {
                $this->formErrors['billing_error'] = true;
                $this->formErrors['billing_errors'] = $checkoutHelper->_getAddressError($result, $billing_data);
                $this->log[] = 'Error saving billing details: ' . implode(', ', $this->formErrors['billing_errors']);
            }


            // Validate stuff that saveBilling doesn't handle
            if(!$this->_isLoggedIn())   {
                $validator = new Zend_Validate_EmailAddress();
                if(!$billing_data['email'] || $billing_data['email'] == '' || !$validator->isValid($billing_data['email'])) {

                    if(is_array($this->formErrors['billing_errors']))   {
                        $this->formErrors['billing_errors'][] = 'email';
                    }
                    else    {
                        $this->formErrors['billing_errors'] = array('email');
                    }

                    $this->formErrors['billing_error'] = true;

                }
                else    {


                    $allow_guest_create_account_validation = false;

                    if($this->settings['registration_mode'] == 'allow_guest')   {
                        if(isset($_POST['create_account']) && $_POST['create_account'] == '1')  {
                            $allow_guest_create_account_validation = true;
                        }
                    }


                    if($this->settings['registration_mode'] == 'require_registration' || $this->settings['registration_mode'] == 'auto_generate_account' || $allow_guest_create_account_validation)  {
                        if($this->_customerEmailExists($billing_data['email'], Mage::app()->getWebsite()->getId()))   {

                            $allow_without_password = $this->settings['registration_order_without_password'];



                            if(!$allow_without_password)    {
                                if(is_array($this->formErrors['billing_errors']))   {
                                    $this->formErrors['billing_errors'][] = 'email';
                                    $this->formErrors['billing_errors'][] = 'email_registered';
                                }
                                else    {
                                    $this->formErrors['billing_errors'] = array('email','email_registered');
                                }
                            }
                            else    {
                            }
                        }
                        else    {

                            $password_errors = array();

                            if(!isset($billing_data['customer_password']) || $billing_data['customer_password'] == '')    {
                                $password_errors[] = 'password';
                            }

                            if(!isset($billing_data['confirm_password']) || $billing_data['confirm_password'] == '')    {
                                $password_errors[] = 'confirm_password';
                            }
                            else    {
                                if($billing_data['confirm_password'] !== $billing_data['customer_password']) {
                                    $password_errors[] = 'password';
                                    $password_errors[] = 'confirm_password';
                                }
                            }

                            if(count($password_errors) > 0) {
                                if(is_array($this->formErrors['billing_errors']))   {
                                    foreach($password_errors as $error) {
                                        $this->formErrors['billing_errors'][] = $error;
                                    }
                                }
                                else    {
                                    $this->formErrors['billing_errors'] = $password_errors;
                                }
                            }
                        }
                    }


                }
            }

            if($this->settings['enable_terms']) {
                if(!isset($post['accept_terms']) || $post['accept_terms'] != '1')   {
                    $this->formErrors['terms_error'] = true;
                }
            }


            if ($this->settings['enable_default_terms'] && $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    //$this->formErrors['terms_error'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->formErrors['agreements_error'] = true;
                }
            }

            $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);

            if($this->differentShippingAvailable()) {
                if(!isset($billing_data['use_for_shipping']) || $billing_data['use_for_shipping'] != '1')   {
                    //$shipping_result = $this->getOnepage()->saveShipping($shipping_data, $shippingAddressId);
                    $shipping_result = Mage::helper('onestepcheckout/checkout')->saveShipping($shipping_data, $shippingAddressId);

                    if(isset($shipping_result['error']))    {
                        $this->formErrors['shipping_error'] = true;
                        $this->formErrors['shipping_errors'] = $checkoutHelper->_getAddressError($shipping_result, $shipping_data, 'shipping');
                    }
                }
                else    {
                    //$shipping_result = $this->getOnepage()->saveShipping($billing_data, $shippingAddressId);
                    $shipping_result = Mage::helper('onestepcheckout/checkout')->saveShipping($billing_data, $customerAddressId);
                }
            }
        }

        // Save shipping method
        $shipping_method = $this->getRequest()->getPost('shipping_method', '');

        if(!$this->isVirtual()){
            //additional checks if the rate is indeed available for chosen shippin address
            $availableRates = $this->getAvailableRates($this->getOnepage()->getQuote()->getShippingAddress()->getGroupedAllShippingRates());
            if(empty($shipping_method) || !in_array($shipping_method,$availableRates)){
                $this->formErrors['shipping_method'] = true;
            }
        }

        if(!$this->isVirtual() )  {
            //$result = $this->getOnepage()->saveShippingMethod($shipping_method);
            $result = Mage::helper('onestepcheckout/checkout')->saveShippingMethod($shipping_method);
            if(isset($result['error']))    {
                $this->formErrors['shipping_method'] = true;
            }
            else    {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
            }
        }




        // Save payment method
        $payment = $this->getRequest()->getPost('payment', array());
        $paymentRedirect = false;

        /**
         * A fix for common one big form problem
         * we rename the fields in template and iterate over subarrays
         * to see if there's any values and set them to main scope
         */
        foreach($payment as $value){
            if(is_array($value) && !empty($value)){
                foreach($value as $key => $realValue){
                    if(!empty($realValue)){
                        $payment[$key]=$realValue;
                    }
                }
            }
        }

        /**
         * unset unnecessary fields
         */
        foreach ($payment as $key => $value){
            if(is_array($value)){
                unset($payment[$key]);
            }
        }

        //echo '<pre>' . print_r($_POST,1) . '</pre>';
        //echo '<pre>' . print_r($payment,1) . '</pre>';

        try {
            if(!empty($payment['method']) && $payment['method'] == 'free'){

                $instance = Mage::helper('payment')->getMethodInstance('free');
                if ($instance->isAvailable($this->getOnepage()->getQuote())) {
                    $instance->setInfoInstance($this->getOnepage()->getQuote()->getPayment());
                    $this->getOnepage()->getQuote()->getPayment()->setMethodInstance($instance);
                }
            }
            //$result = $this->getOnepage()->savePayment($payment);
            $result = Mage::helper('onestepcheckout/checkout')->savePayment($payment);
            $paymentRedirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

        }
        catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        }
        catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        if(isset($result['error'])) {

            if($result['error'] == 'Can not retrieve payment method instance')  {
                $this->formErrors['payment_method'] = true;
            }
            else    {
                $this->formErrors['payment_method_error']  = $result['error'];
            }
        }


        if(!$this->hasFormErrors()) {

            if($this->settings['enable_newsletter']) {
                // Handle newsletter
                $subscribe_newsletter = $this->getRequest()->getPost('subscribe_newsletter');
                $registration_mode = $this->settings['registration_mode'];
                if(!empty($subscribe_newsletter) && ($registration_mode != 'require_registration' && $registration_mode != 'auto_generate_account') && !$this->getRequest()->getPost('create_account'))  {
                    $model = Mage::getModel('newsletter/subscriber');
                    $result = $model->loadByEmail($this->email);
                    if(!$result->getId()){
                        Mage::getModel('newsletter/subscriber')->subscribe($this->email);
                    }
                }
            }

            if($paymentRedirect && $paymentRedirect != '')  {
                Header('Location: ' . $paymentRedirect);
                die();
            }

            if( $this->_isLoggedIn() )  {
                // User is logged in
                // Place order as registered customer

                $this->_saveOrder();
                $this->log[] = 'Saving order as a logged in customer';

            }
            else    {

                if( $this->_isEmailRegistered() )   {

                    $registration_mode = $this->settings['registration_mode'];
                    $allow_without_password = $this->settings['registration_order_without_password'];

                    if($registration_mode == 'require_registration' || $registration_mode == 'auto_generate_account')   {

                        if($allow_without_password) {

                            // Place order on the emails account without the password
                            $this->setCustomerAfterPlace($this->_getCustomer());

                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();

                        }
                        else    {
                            // This should not happen, because validation should handle it
                            die('Validation did not handle it');
                        }
                    }
                    else    {
                        $this->getOnepage()->saveCheckoutMethod('guest');
                        $this->_saveOrder();
                    }


                    // Place order as customer with same e-mail address
                    $this->log[] = 'Save order on existing account with email address';




                }
                else    {

                    if($this->settings['registration_mode'] == 'require_registration')  {

                        // Save as register
                        $this->log[] = 'Save order as REGISTER';
                        $this->getOnepage()->saveCheckoutMethod('register');
                        $this->_saveOrder();
                    }
                    elseif($this->settings['registration_mode'] == 'allow_guest')   {
                        if(isset($_POST['create_account']) && $_POST['create_account'] == '1')  {
                            $this->getOnepage()->saveCheckoutMethod('register');
                            $this->_saveOrder();
                        }
                        else    {
                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();
                        }
                    }
                    else    {


                        $registration_mode = $this->settings['registration_mode'];

                        if($registration_mode == 'auto_generate_account')   {
                            $this->getOnepage()->saveCheckoutMethod('register');
                            $this->_saveOrder();
                        }
                        else    {
                            $this->getOnepage()->saveCheckoutMethod('guest');
                            $this->_saveOrder();
                        }
                    }
                }
            }
        }
    }

    protected function setCustomerAfterPlace($customer)
    {
        $this->customer_after_place_order = $customer;
    }

    protected function afterPlaceOrder()
    {
        $customer = $this->customer_after_place_order;
        $order_id = $this->getOnepage()->getLastOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);

        if($customer)   {
            $order->setCustomerId($customer->getId());
            $order->setCustomerIsGuest(false);
            $order->setCustomerGroupId($customer->getGroupId());
            $order->save();
        }
    }

    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    protected function _getCustomer()
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($this->email);

        if($model->getId() == NULL) {
            return false;
        }

        return $model;
    }

    protected function _isEmailRegistered()
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($this->email);

        if($model->getId() == NULL) {
            return false;
        }

        return true;
    }

    public function validateMinimumAmount()
    {
        return $this->getQuote()->validateMinimumAmount();
    }

    public function canCheckout()
    {
        if($this->getQuote()->getItemsSummaryQty() == 0)    {
            return false;
        }

        return true;
    }

    protected function _saveOrder()
    {
        // Hack to fix weird Magento payment behaviour
        $payment = $this->getRequest()->getPost('payment', false);
        if($payment) {
            /**
             * A fix for common one big form problem
             * we rename the fields in template and iterate over subarrays
             * to see if there's any values and set them to main scope
             */
            foreach($payment as $value){
                if(is_array($value) && !empty($value)){
                    foreach($value as $key => $realValue){
                        if(!empty($realValue)){
                            $payment[$key]=$realValue;
                        }
                    }
                }
            }

            /**
             * unset unnecessary fields
             */
            foreach ($payment as $key => $value){
                if(is_array($value)){
                    unset($payment[$key]);
                }
            }
            $this->getOnepage()->getQuote()->getPayment()->importData($payment);

            $ccSaveAllowedMethods = array('ccsave');
            $method = $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();

            if(in_array($method->getCode(), $ccSaveAllowedMethods)){
                $info = $method->getInfoInstance();
                $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
            }

        }


        try {
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            $order = $this->getOnepage()->saveOrder();
        } catch(Exception $e)   {
            //need to activate
            $this->getOnepage()->getQuote()->setIsActive(true);
            //need to recalculate
            $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectTotals();
            $error = $e->getMessage();
            $this->formErrors['unknown_source_error'] = $error;
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $error);
            return;
            //die('Error: ' . $e->getMessage());
        }

        $this->afterPlaceOrder();

        $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();

        if($redirectUrl)    {
            $redirect = $redirectUrl;
        } else {
            $this->getOnepage()->getQuote()->setIsActive(false);
            $this->getOnepage()->getQuote()->save();
            $redirect = $this->getUrl('checkout/onepage/success');
            //$this->_redirect('checkout/onepage/success', array('_secure'=>true));
        }

        Header('Location: ' . $redirect);
        exit();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function isUseBillingAddressForShipping()
    {
        if (($this->getQuote()->getIsVirtual())
        || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getCountryHtmlSelect($type)
    {
        if($type == 'billing')  {
            $address = $this->getQuote()->getBillingAddress();
            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getBillingAddress();
             */

        }
        else    {
            $address = $this->getQuote()->getShippingAddress();

            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getShippingAddress();
             */
        }

        $countryId = $address->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
        ->setName($type.'[country_id]')
        ->setId($type.':country_id')
        ->setTitle(Mage::helper('checkout')->__('Country'))
        ->setClass('validate-select')
        ->setValue($countryId)
        ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }

    public function getBillingFieldsOrder($fields = array()){

        $fieldsAvailable = array(
            'name' => array('fields' => array('firstname','lastname')),
            'email-phone' => array('fields' =>array('email','telephone')),
            'street' => array(),
            'country_id' => array(),
            'postcode-regionid' => array('fields' =>array('postcode','region_id')),
            'city' => array(),
            'company-fax' => array('fields' => array('company','fax')),
            'taxvat' => array(),
            'dob' => array(),
            'gender' => array(),
            'create_account' => array(),
            'password' => array('has_li' => 1, 'fields' => array('password','confirm_password')),
            'save_in_address_book' => array('has_li' => 1),
            'use_for_shipping_yes' => array(),
        );
        $settings = $this->settings['sortordering_fields'];
        $tmp = array();
        foreach ($fieldsAvailable as $key => $value){
            if(empty($value['fields'])){
                if(!empty($settings[$key]) && !empty($fields[$key]) ){
                    $tmp[$settings[$key]]['fields'][] = $fields[$key];
                    if(!empty($value['has_li'])){
                        $tmp[$settings[$key]]['has_li']=1;
                    }
                }
            } else {
                foreach($value['fields'] as $subfield){
                    if(!empty($settings[$subfield]) && !empty($fields[$subfield]) ){
                        if(empty($placeholder)){
                            $placeholder = $settings[$subfield];
                        }
                        $tmp[$placeholder]['fields'][$settings[$subfield]] = $fields[$subfield];
                    }
                }
                if(!empty($value['has_li']) && !empty($placeholder)){
                        $tmp[$placeholder]['has_li']=1;
                }
                if(!empty($placeholder)){
                    ksort($tmp[$placeholder]['fields']);
                    unset($placeholder);
                }

            }
        }
        ksort($tmp);
        $fields = $tmp ;

        return $fields;
    }

    public function getShippingFieldsOrder($fields = array()){
        $fieldsAvailable = array(
            'name' => array('fields' => array('firstname','lastname')),
            'telephone' => array(),
            'street' => array(),
            'country_id' => array(),
            'postcode-regionid' => array('fields' =>array('postcode','region_id')),
            'city' => array(),
            'company-fax' => array('fields' => array('company','fax')),
            'save_in_address_book' => array('has_li' => 1),
        );
        $settings = $this->settings['sortordering_fields'];
        $tmp = array();
        foreach ($fieldsAvailable as $key => $value){
            if(empty($value['fields'])){
                if(!empty($settings[$key]) && !empty($fields[$key]) ){
                    $tmp[$settings[$key]]['fields'][] = $fields[$key];
                    if(!empty($value['has_li'])){
                        $tmp[$settings[$key]]['has_li']=1;
                    }
                }
            } else {
                foreach($value['fields'] as $subfield){
                    if(!empty($settings[$subfield]) && !empty($fields[$subfield]) ){
                        if(empty($placeholder)){
                            $placeholder = $settings[$subfield];
                        }
                        $tmp[$placeholder]['fields'][$settings[$subfield]] = $fields[$subfield];
                    }
                }
                if(!empty($value['has_li'])){
                        $tmp[$placeholder]['has_li']=1;
                }
                if(!empty($placeholder)){
                    ksort($tmp[$placeholder]['fields']);
                    unset($placeholder);
                }
            }
        }
        ksort($tmp);
        $fields = $tmp ;

        return $fields;
    }

    /**
     * check if e-mail address is subscribed to newsletter
     *
     * @param $email string
     * @return boolean
     */
    public function isSubscribed ($email = null)
    {
        $isSubscribed = false;

        if (! empty($email)) {
            try {
                $result = Mage::getModel('newsletter/subscriber')->loadByEmail(
                $email);
                if (is_object($result) && $result->getSubscriberStatus() == 1) {
                    $isSubscribed = true;
                }
            } catch (Exception $e) {}
        }

        return $isSubscribed;
    }
}
