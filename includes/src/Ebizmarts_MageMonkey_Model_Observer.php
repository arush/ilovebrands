<?php

/**
 * Events Observer model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Observer
{
	/**
	 * Handle Subscriber object saving process
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function handleSubscriber(Varien_Event_Observer $observer)
	{

		if(!Mage::helper('monkey')->canMonkey()){
			return $observer;
		}

		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();

		if( $subscriber->getBulksync() ){
			return $observer;
		}

		$subscriber->setImportMode(TRUE);

		$email  = $subscriber->getSubscriberEmail();
		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());

		$isConfirmNeed = FALSE;
		if( !Mage::helper('monkey')->isAdmin() &&
			(Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId()) == 1) ){
			$isConfirmNeed = TRUE;
		}

		//New subscriber, just add
		if( $subscriber->isObjectNew() ){

			if( TRUE === $isConfirmNeed ){
				$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
			}

			$mergeVars = $this->_mergeVars($subscriber);
			Mage::getSingleton('monkey/api')
								->listSubscribe($listId, $email, $mergeVars, 'html', $isConfirmNeed);

		}else{

			$oldSubscriber = Mage::getModel('newsletter/subscriber')
								->load($subscriber->getId());

			$status        = (int)$subscriber->getData('subscriber_status');
			$oldstatus     = (int)$oldSubscriber->getData('subscriber_status');

			if( $status !== $oldstatus ){ //Status change

				//Unsubscribe customer
				if($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){

					$rs = Mage::getSingleton('monkey/api')
									->listUnsubscribe($listId, $email);
					if($rs !== TRUE){
						Mage::throwException($rs);
					}

				}else if($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){

					if( TRUE === $isConfirmNeed ){
						$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
					}

					$rs = Mage::getSingleton('monkey/api')
									->listSubscribe($listId, $email, $this->_mergeVars($subscriber), 'html', $isConfirmNeed);
					if($rs !== TRUE){
						Mage::throwException($rs);
					}

				}

			}

		}
	}

	/**
	 * Handle Subscriber deletion from Magento, unsubcribes email from MailChimp
	 * and sends the delete_member flag so the subscriber gets deleted.
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function handleSubscriberDeletion(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();
		$subscriber->setImportMode(TRUE);

		if( $subscriber->getBulksync() ){
			return $observer;
		}

		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());

		Mage::getSingleton('monkey/api', array('store' => $subscriber->getStoreId()))
									->listUnsubscribe($listId, $subscriber->getSubscriberEmail(), TRUE);

	}

	/**
	 * Check for conflicts with rewrite on Core/Email_Template
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function loadConfig(Varien_Event_Observer $observer)
	{
		$action = $observer->getEvent()->getControllerAction();

        //Do nothing for data saving actions
        if ($action->getRequest()->isPost() || $action->getRequest()->getQuery('isAjax')) {
            return $observer;
        }

		if('monkey' !== $action->getRequest()->getParam('section')){
			return $observer;
		}

		$myRewrite = 'Ebizmarts_MageMonkey_Model_Email_Template';
		$modelName = Mage::app()->getConfig()->getModelClassName('core/email_template');

		if(Mage::helper('monkey')->useTransactionalService() && ($myRewrite !== $modelName)){
			Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('monkey')->__('Transactional Emails via MailChimp are not working because of a conflict with: "%s"', $modelName)
            );
		}

		return $observer;
	}

	/**
	 * Handle save of System -> Configuration, section <monkey>
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function saveConfig(Varien_Event_Observer $observer)
	{

		$store  = is_null($observer->getEvent()->getStore()) ? 'default': $observer->getEvent()->getStore();
		$post   = Mage::app()->getRequest()->getPost();
		$request = Mage::app()->getRequest();

		if( !isset($post['groups']) ){
			return $observer;
		}
		//Chequear que el índice exista

		$apiKey = (string)$post['groups']['general']['fields']['apikey']['value'];

		if(!$apiKey){
			return $observer;
		}

		$selectedLists = array();
		$selectedLists []= $post['groups']['general']['fields']['list']['value'];

		$additionalLists = $post['groups']['general']['fields']['additional_lists']['value'];
		if(is_array($additionalLists)){
			$selectedLists = array_merge($selectedLists, $additionalLists);
		}

		$webhooksKey = Mage::helper('monkey')->getWebhooksKey($store);
		$hookUrl  = Mage::app()->getStore($store)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, FALSE);
		$hookUrl .= Ebizmarts_MageMonkey_Model_Monkey::WEBHOOKS_PATH . $webhooksKey;

		$api   = Mage::getSingleton('monkey/api', array('apikey' => $apiKey));

		//Validate API KEY
		$api->ping();
		if($api->errorCode){
			Mage::getSingleton('adminhtml/session')->addError($api->errorMessage);
			return $observer;
		}

		$lists = $api->lists();

		foreach($lists['data'] as $list){

			$webHooks = $api->listWebhooks($list['id']);

			if(!empty($webHooks)){
				foreach($webHooks as $whook){
					$chunk = (string)substr($whook['url'], strrpos($whook['url'], '/')+1, strlen($whook['url']));

					if((string)$webhooksKey === $chunk){
						$api->listWebhookDel($list['id'], $whook['url']);
					}
				}
			}

			if(in_array($list['id'], $selectedLists)){

				 /**
				  * Customer Group - Interest Grouping
				  */
				    $config = Mage::getModel('core/config_data');

					$interestGroupings = $api->listInterestGroupings($list['id']);
					$groupingName = Mage::helper('monkey')->getCustomerGroupingName();

					//Check that Grouping not exists
					$groupingExists = Mage::helper('monkey')->customerGroupGroupingExists($interestGroupings);

					if(FALSE === $groupingExists){
						$magentoGroups     = Mage::helper('customer')->getGroups()
												->toOptionHash();

						$groupingId = NULL;

						if($api->errorCode){
							//Maybe the error is telling us that there are no groupings yet
							//Check for "This list does not have interest groups enabled"
							if($api->errorCode == '211'){
								$groupingId = $api->listInterestGroupingAdd($list['id'], $groupingName, 'dropdown', $magentoGroups);
							}
						}else{
							$groupingId = $api->listInterestGroupingAdd($list['id'], $groupingName, 'dropdown', $magentoGroups);
						}

						if($groupingId && is_int($groupingId)){

							$website = $request->getParam('website');
							$store   = $request->getParam('store');
							if ($store) {
								$scope   = 'stores';
								$scopeId = (int)Mage::getConfig()->getNode('stores/' . $store . '/system/store/id');
							} elseif ($website) {
								$scope   = 'websites';
								$scopeId = (int)Mage::getConfig()->getNode('websites/' . $website . '/system/website/id');
							} else {
								$scope   = 'default';
								$scopeId = 0;
							}

							$config
							->setScope($scope)
							->setScopeId($scopeId)
							->setPath('monkey/groupings/' . $list['id'])
							->setValue($groupingId)
							->save();
						}
					}
				 /**
				  * Customer Group - Interest Grouping
				  */

				/**
				 * Adding Webhooks
				 */
				$api->listWebhookAdd($list['id'], $hookUrl);

				//If webhook was not added, add a message on Admin panel
				if($api->errorCode && Mage::helper('monkey')->isAdmin()){

					$message = Mage::helper('monkey')->__('Could not add Webhook "%s" for list "%s", error code %s, %s', $hookUrl, $list['name'], $api->errorCode, $api->errorMessage);
					Mage::getSingleton('adminhtml/session')->addError($message);

				}
				/**
				 * Adding Webhooks
				 */
			}

		}

	}

	/**
	 * Update customer after_save event observer
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function updateCustomer(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		$customer = $observer->getEvent()->getCustomer();

		$oldEmail = $customer->getOrigData('email');
		if(!$oldEmail){
			return $observer;
		}

		$mergeVars = $this->_mergeVars($customer, TRUE);
		$api   = Mage::getSingleton('monkey/api', array('store' => $customer->getStoreId()));

		$lists = $api->listsForEmail($oldEmail);

		if(is_array($lists)){
			foreach($lists as $listId){
				$api->listUpdateMember($listId, $oldEmail, $mergeVars);
			}
		}

		return $observer;
	}

	/**
	 * Add flag on session to tell the module if on success page should subscribe customer
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function registerCheckoutSubscribe(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}
		$subscribe = Mage::app()->getRequest()->getPost('magemonkey_subscribe');

		if(!is_null($subscribe)){
			Mage::getSingleton('core/session')->setMonkeyCheckout($subscribe);
		}
	}

	/**
	 * Subscribe customer to Newsletter if flag on session is present
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function registerCheckoutSuccess(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}
		$sessionFlag = Mage::getSingleton('core/session')->getMonkeyCheckout(TRUE);

		if($sessionFlag){
			$orderId = (int)current($observer->getEvent()->getOrderIds());

			if($orderId){
				$order = Mage::getModel('sales/order')->load($orderId);
				if( $order->getId() ){

						//Guest Checkout
						if( (int)$order->getCustomerGroupId() === Mage_Customer_Model_Group::NOT_LOGGED_IN_ID ){
							Mage::helper('monkey')->registerGuestCustomer($order);
						}

						$subscriber = Mage::getModel('newsletter/subscriber')
							->subscribe($order->getCustomerEmail());
				}
			}
		}
	}

	/**
	 * Get Mergevars
	 *
	 * @param null|Mage_Customer_Model_Customer $object
	 * @param bool $includeEmail
	 * @return array
	 */
	protected function _mergeVars($object = NULL, $includeEmail = FALSE)
	{
		//Initialize as GUEST customer
		$customer = new Varien_Object;

		$regCustomer   = Mage::registry('current_customer');
		$guestCustomer = Mage::registry('mc_guest_customer');

		if( Mage::helper('customer')->isLoggedIn() ){
			$customer = Mage::helper('customer')->getCustomer();
		}elseif($regCustomer){
			$customer = $regCustomer;
		}elseif($guestCustomer){
			$customer = $guestCustomer;
		}else{
			if(is_null($object)){
				$customer->setEmail($object->getSubscriberEmail())
					 ->setStoreId($object->getStoreId());
			}else{
				$customer = $object;
			}

		}

		$mergeVars = Mage::helper('monkey')->getMergeVars($customer, $includeEmail);

		return $mergeVars;
	}

	/**
	 * Add mass action option to Sales -> Order grid in admin panel to send orders to MC (Ecommerce360)
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function massActionOption($observer)
    {
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}
        $block = $observer->getEvent()->getBlock();

        if(get_class($block) == 'Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction') {

            if($block->getRequest()->getControllerName() == 'sales_order') {

                $block->addItem('magemonkey_ecommerce360', array(
                    'label'=> Mage::helper('monkey')->__('Send to MailChimp'),
                    'url'  => Mage::app()->getStore()->getUrl('monkey/adminhtml_ecommerce/masssend', Mage::app()->getStore()->isCurrentlySecure() ? array('_secure'=>true) : array()),
                ));

            }
        }
    }

}
