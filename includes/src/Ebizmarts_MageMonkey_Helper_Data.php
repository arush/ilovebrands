<?php

/**
 * Mage Monkey default helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * Utility to check if admin is logged in
	 *
	 * @return bool
	 */
	public function isAdmin()
	{
		return Mage::getSingleton('admin/session')->isLoggedIn();
	}

    /**
     * Check if Magento is EE
     *
     * @return bool
     */
    public function isEnterprise()
    {
        return is_object(Mage::getConfig()->getNode('global/models/enterprise_enterprise'));
    }

	/**
	 * Return Webhooks security key for given store
	 *
	 * @param mixed $store Store object, or Id, or code
	 * @param string $listId Optional listid to retrieve store code from it
	 * @return string
	 */
	public function getWebhooksKey($store, $listId = null)
	{
		if( !is_null($listId) ){
			$store = $this->getStoreByList($listId, TRUE);
		}

		$crypt = md5((string)Mage::getConfig()->getNode('global/crypt/key'));
		$key   = substr($crypt, 0, (strlen($crypt)/2));

		return ($key . $store);
	}

	public function filterShowGroupings($interestGroupings)
	{
		if(is_array($interestGroupings)){

			$customGroupings = Mage::getConfig()->getNode('default/monkey/custom_groupings')
																					->asArray();

			foreach($interestGroupings as $key => $group){

				if(TRUE === in_array($group['name'], $customGroupings)){
					unset($interestGroupings[$key]);
				}

			}
		}

		return $interestGroupings;
	}

	/**
	 * Check if CustomerGroup grouping already exists on MC
	 *
	 * @param array $groupings
	 * @return bool
	 */
	public function customerGroupGroupingExists($interestGroupings)
	{
		$exists = FALSE;
		if(is_array($interestGroupings)){
			foreach($interestGroupings as $group){
				if($group['name'] == $this->getCustomerGroupingName()){
					$exists = TRUE;
					break;
				}
			}
		}

		return $exists;
	}

	/**
	 * Return customer groping name to be used when creating a grouping to store
	 * Magento customer groups
	 *
	 * @return string
	 */
	public function getCustomerGroupingName()
	{
		return (string)Mage::getConfig()->getNode('default/monkey/custom_groupings/customer_grouping_name');
	}

	/**
	 * Get module User-Agent to use on API requests
	 *
	 * @return string
	 */
	public function getUserAgent()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		$aux = (array_key_exists('Enterprise_Enterprise',$modulesArray))? 'EE' : 'CE' ;
		$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
		$version = strpos(Mage::getVersion(),'-')? substr(Mage::getVersion(),0,strpos(Mage::getVersion(),'-')) : Mage::getVersion();
		return (string)'MageMonkey'.$v.'/Mage'.$aux.$version;
	}

	/**
	 * Return Mandrill API key
	 *
	 * @param string $store
	 * @return string Api Key
	 */
	public function getMandrillApiKey($store = null)
	{
		if(is_null($store)){
			$key = $this->config('mandrill_apikey');
		}else{
			$curstore = Mage::app()->getStore();
			Mage::app()->setCurrentStore($store);
			$key = $this->config('mandrill_apikey', $store);
			Mage::app()->setCurrentStore($curstore);
		}

		return $key;
	}

	/**
	 * Return MC API key for given store, if none is given
	 * default key is returned
	 *
	 * @param string $store
	 * @return string Api Key
	 */
	public function getApiKey($store = null)
	{
		if(is_null($store)){
			$key = $this->config('apikey');
		}else{
			$curstore = Mage::app()->getStore();
			Mage::app()->setCurrentStore($store);
			$key = $this->config('apikey', $store);
			Mage::app()->setCurrentStore($curstore);
		}

		return $key;
	}

	/**
	 * Logging facility
	 *
	 * @param mixed $data Message to save to file
	 * @param string $filename log filename, default is <Monkey.log>
	 * @return Mage_Core_Model_Log_Adapter
	 */
	public function log($data, $filename = 'Monkey.log')
	{
		return Mage::getModel('core/log_adapter', $filename)->log($data);
	}

	/**
	 * Get module configuration value
	 *
	 * @param string $value
	 * @param string $store
	 * @return mixed Configuration setting
	 */
	public function config($value, $store = null)
	{
		$store = is_null($store) ? Mage::app()->getStore() : $store;

		$configscope = Mage::app()->getRequest()->getParam('store');
		if( $configscope ){
			$store = $configscope;
		}

		return Mage::getStoreConfig("monkey/general/$value", $store);
	}

	/**
	 * Check if config setting <checkout_subscribe> is enabled
	 *
	 * @return bool
	 */
	public function canCheckoutSubscribe()
	{
		return (bool)($this->config('checkout_subscribe') != 0);
	}

	/**
	 * Check if Ecommerce360 integration is enabled
	 *
	 * @return bool
	 */
	public function ecommerce360Active()
	{
		return (bool)($this->config('ecommerce360') != 0);
	}

	/**
	 * Check if Transactional Email via MC is enabled
	 *
	 * @return bool
	 */
	public function useTransactionalService()
	{
		return Mage::getStoreConfigFlag("monkey/general/transactional_emails");
	}

	/**
	 * Check if Ebizmarts_MageMonkey module is enabled
	 *
	 * @return bool
	 */
	public function canMonkey()
	{
		return (bool)((int)$this->config('active') !== 0);
	}

	/**
	 * Get default MC listId for given storeId
	 *
	 * @param string $store
	 * @return string $list
	 */
	public function getDefaultList($store)
	{
		$curstore = Mage::app()->getStore();
		Mage::app()->setCurrentStore($store);
			$list = $this->config('list', $store);
		Mage::app()->setCurrentStore($curstore);
		return $list;
	}

	/**
	 * Get which store is associated to given $mcListId
	 *
	 * @param string $mcListId
	 * @param bool $includeDefault Include <default> store or not on result
	 * @return string $store
	 */
	public function getStoreByList($mcListId, $includeDefault = FALSE)
	{
        $list = Mage::getModel('core/config_data')->getCollection()
            	->addValueFilter($mcListId)->getFirstItem();

        $store = null;
        if($list->getId()){

        	$isDefault = (bool)($list->getScope() == 'default');
        	if(!$isDefault && !$includeDefault){
        		$store = (string)Mage::app()->getStore($list->getScopeId())->getCode();
        	}else{
        		$store = $list->getScope();
        	}

        }

        return $store;
	}

	/**
	 * Check if current request is a Webhooks request
	 *
	 * @return bool
	 */
	public function isWebhookRequest()
	{
		$rq            = Mage::app()->getRequest();
		$monkeyRequest = (string)'monkeywebhookindex';
		$thisRequest   = (string)($rq->getRequestedRouteName() . $rq->getRequestedControllerName() . $rq->getRequestedActionName());

		return (bool)($monkeyRequest === $thisRequest);
	}

	/**
	 * Get config setting <map_fields>
	 *
	 * @return array|FALSE
	 */
	public function getMergeMaps($storeId)
	{
		return unserialize( $this->config('map_fields', $storeId) );
	}

	/**
	 * Get progress bar HTML code
	 *
	 * @param integer $complete Processed qty so far
	 * @param integer $total Total qty to process
	 * @return string
	 */
	public function progressbar($complete, $total)
	{
		if($total == 0){
			return;
		}
		$percentage = round(($complete * 100) / $total, 0);

		$barStyle = '';
		if($percentage > 0){
 			$barStyle = " style=\"width: $percentage%\"";
		}

		$html = "<div id=\"bar-progress-bar\" class=\"bar-all-rounded\">\n";
		$html .= "<div id=\"bar-progress-bar-percentage\" class=\"bar-all-rounded\"$barStyle>";
		$html .= "$percentage% ($complete of $total)";
		//<progress value="75" max="100">3/4 complete</progress>
			//if ($percentage > 5) {$html .= "$percentage% ($complete of $total)";} else {$html .= "<div class=\"bar-spacer\">&nbsp;</div>";}
		$html .= "</div></div>";

		return $html;
	}

	/**
	 * Return Merge Fields mapped to Magento attributes
	 *
	 * @param object $customer
	 * @param bool $includeEmail
	 * @param integer $websiteId
	 * @return array
	 */
	public function getMergeVars($customer, $includeEmail = FALSE, $websiteId = NULL)
	{
		$merge_vars   = array();
        $maps         = $this->getMergeMaps($customer->getStoreId());

		if(!$maps){
			return;
		}

		$request = Mage::app()->getRequest();

		//Add Customer data to Subscriber if is Newsletter_Subscriber is Customer
		if($customer->getCustomerId()){
			$customer->addData(Mage::getModel('customer/customer')->load($customer->getCustomerId())
									->toArray());
		}

		foreach($maps as $map){

			$customAtt = $map['magento'];
			$chimpTag  = $map['mailchimp'];

			if($chimpTag && $customAtt){

				$key = strtoupper($chimpTag);

				switch ($customAtt) {
					case 'gender':
							$val = (int)$customer->getData(strtolower($customAtt));
							if($val == 1){
								$merge_vars[$key] = 'Male';
							}elseif($val == 2){
								$merge_vars[$key] = 'Female';
							}
						break;
					case 'dob':
							$dob = (string)$customer->getData(strtolower($customAtt));
							if($dob){
								$merge_vars[$key] = (substr($dob, 5, 2) . '/' . substr($dob, 8, 2));
							}
						break;
					case 'billing_address':
					case 'shipping_address':

						$addr = explode('_', $customAtt);
						$address = $customer->{'getPrimary'.ucfirst($addr[0]).'Address'}();
						if($address){
							$merge_vars[$key] = array(
																	'addr1'   => $address->getStreet(1),
														   			'addr2'   => $address->getStreet(2),
															   		'city'    => $address->getCity(),
															   		'state'   => (!$address->getRegion() ? $address->getCity() : $address->getRegion()),
															   		'zip'     => $address->getPostcode(),
															   		'country' => $address->getCountryId()
															   	  );
						}

						break;
					case 'date_of_purchase':

						$last_order = Mage::getResourceModel('sales/order_collection')
                        	->addFieldToFilter('customer_email', $customer->getEmail())
                        	->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                        	->setOrder('created_at', 'desc')
                        	->getFirstItem();
	                    if ( $last_order->getId() ){
	                    	$merge_vars[$key] = Mage::helper('core')->formatDate($last_order->getCreatedAt());
	                    }

						break;
					case 'ee_customer_balance':

						$merge_vars[$key] = '';

						if($this->isEnterprise() && $customer->getId()){

							if (Mage::app()->getStore()->isAdmin()) {
								$websiteId = is_null($websiteId) ? Mage::app()->getStore()->getWebsiteId() : $websiteId;
							}

							$balance = Mage::getModel('enterprise_customerbalance/balance')
									  ->setWebsiteId($websiteId)
									  ->setCustomerId($customer->getId())
									  ->loadByCustomer();

							$merge_vars[$key] = $balance->getAmount();

						}

						break;
					default:

						if( ($value = (string)$customer->getData(strtolower($customAtt)))
							OR ($value = (string)$request->getPost(strtolower($customAtt))) ){
							$merge_vars[$key] = $value;
						}

						break;
				}

			}
		}

		//GUEST
		if( !$customer->getId() ){
			$guestFirstName = $this->config('guest_name', $customer->getStoreId());
			$guestLastName  = $this->config('guest_lastname', $customer->getStoreId());

			if($guestFirstName){
				$merge_vars['FNAME'] = $guestFirstName;
			}
			if($guestLastName){
				$merge_vars['LNAME'] = $guestLastName;
			}
		}
		//GUEST

		if($includeEmail){
			$merge_vars['EMAIL'] = $customer->getEmail();
		}

		$groups = $customer->getListGroups();
		$groupings = array();
		if(is_array($groups) && count($groups)){
			foreach($groups as $groupId => $grupoptions){
				$groupings[] = array(
									 'id' => $groupId,
								     'groups' => (is_array($grupoptions) ? implode(', ', $grupoptions) : $grupoptions)
								    );
			}
		}

		//Add customer group for logged in customers
		if( $customer->getId() && $customer->getMcListId()){

			$groupId = (int)Mage::getStoreConfig("monkey/groupings/" . $customer->getMcListId(), $customer->getStoreId());
			if($groupId){
				$groups = Mage::helper('customer')->getGroups()->toOptionHash();
				$groupings[] = array(
					'id'     => $groupId,
					'groups' => $groups[$customer->getGroupId()],
				);
			}

		}

		$merge_vars['GROUPINGS'] = $groupings;

		//magemonkey_mergevars_after
		$blank = new Varien_Object;
		Mage::dispatchEvent('magemonkey_mergevars_after',
            					array('vars' => $merge_vars, 'customer' => $customer, 'newvars' => $blank));
		if($blank->hasData()){
			$merge_vars = array_merge($merge_vars, $blank->toArray());
		}
		//magemonkey_mergevars_after

		return $merge_vars;
	}

	/**
	 * Register on Magento's registry GUEST customer data for MergeVars for on checkout subscribe
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return void
	 */
	public function registerGuestCustomer($order)
	{

		if( Mage::registry('mc_guest_customer') ){
			return;
		}

		$customer = new Varien_Object;

		$customer->setId(time());
		$customer->setEmail($order->getBillingAddress()->getEmail());
		$customer->setStoreId($order->getStoreId());
		$customer->setFirstname($order->getBillingAddress()->getFirstname());
		$customer->setLastname($order->getBillingAddress()->getLastname());
		$customer->setPrimaryBillingAddress($order->getBillingAddress());
		$customer->setPrimaryShippingAddress($order->getShippingAddress());

		Mage::register('mc_guest_customer', $customer, TRUE);

	}


	/**
	 * Create a Magento's customer account for given data
	 *
	 * @param array $accountData
	 * @param integer $websiteId ID of website to associate customer to
	 * @return Mage_Customer_Model_Customer
	 */
	public function createCustomerAccount($accountData, $websiteId)
	{
		$customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);

		if(!isset($accountData['firstname']) OR empty($accountData['firstname'])){
			$accountData['firstname'] = $this->__('Store');
		}
		if(!isset($accountData['lastname']) OR empty($accountData['lastname'])){
			$accountData['lastname'] = $this->__('Guest');
		}

		$customerForm = Mage::getModel('customer/form');
    	$customerForm->setFormCode('customer_account_create')
        	->setEntity($customer)
        	->initDefaultValues();
        // emulate request
        $request = $customerForm->prepareRequest($accountData);
        $customerData    = $customerForm->extractData($request);
        $customerForm->restoreData($customerData);

		$customerErrors = $customerForm->validateData($customerData);

		if($customerErrors){
            $customerForm->compactData($customerData);

            $pwd = $customer->generatePassword(8);
            $customer->setPassword($pwd);
            $customer->setConfirmation($pwd);


			/**
			 * Handle Address related Data
			 */
			$billing = $shipping = null;
			if(isset($accountData['billing_address']) && !empty($accountData['billing_address'])){
				$this->_McAddressToMage($accountData, 'billing', $customer);
			}
			if(isset($accountData['shipping_address']) && !empty($accountData['shipping_address'])){
				$this->_McAddressToMage($accountData, 'shipping', $customer);
			}
			/**
			 * Handle Address related Data
			 */

            $customerErrors = $customer->validate();
            if (is_array($customerErrors) && count($customerErrors)) {

                //TODO: Do something with errors.

            }else{
            	$customer->save();

				if ( $customer->isConfirmationRequired() ){
                    $customer->sendNewAccountEmail('confirmation');
				}

            }
		}

		return $customer;
	}

	/**
	 * Parse MailChimp <address> MergeField type to Magento's address object
	 *
	 * @param array $data MC address data
	 * @param string $type billing or shipping
	 * @param Mage_Customer_Model_Customer $customer
	 * @return array Empty if noy errors, or a list of errors in an Array
	 */
	protected function _McAddressToMage(array $data, $type, $customer)
	{
		$addressData = $data["{$type}_address"];
		$address = explode(str_repeat(chr(32), 2), $addressData);
		list($addr1, $addr2, $city, $state, $zip, $country) = $address;

		$region = Mage::getModel('directory/region')->loadByName($state, $country);

		$mgAddress = array(
							'firstname' => $data['firstname'],
							'lastname' => $data['lastname'],
							'street'  => array($addr1, $addr2),
							'city' => $city,
							'country_id' => $country,
							'region' => $state,
							'region_id' => (!is_null($region->getId()) ? $region->getId() : null),
							'postcode' => $zip,
							'telephone' => 'not_provided',
						  );

        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

		$addrrequest = $addressForm->prepareRequest($mgAddress);
        $addressData = $addressForm->extractData($addrrequest);
        $addressErrors  = $addressForm->validateData($addressData);

        $errors = array();
        if ($addressErrors === true) {
            $address->setId(null)
            	->setData("is_default_{$type}", TRUE);
            $addressForm->compactData($addressData);
            $customer->addAddress($address);

            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            }
        } else {
            $errors = array_merge($errors, $addressErrors);
        }

		return $errors;
	}

}
