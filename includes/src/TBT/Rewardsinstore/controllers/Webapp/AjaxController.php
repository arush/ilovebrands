<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * The entrance point for all AJAX requests from the Instore webapp
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
require_once ('app/code/community/TBT/Rewardsinstore/controllers/LoyaltyController.php');
class TBT_Rewardsinstore_Webapp_AjaxController extends TBT_Rewardsinstore_LoyaltyController
{
    protected function _construct()
    {
        parent::_construct();
        $this->_usedModuleName = "rewardsinstore";
    }
    
    /**
     * Acl permissions
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewards/instore/webapp');
    }
    
    public function preDispatch()
    {
        $loginData = $this->getRequest()->getPost('login');
        $username = $loginData['username'];
        
        parent::preDispatch();
        
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $loginData = $this->getRequest()->getPost('login');
            Mage::dispatchEvent('rewardsinstore_storefront_login_failure', array(
                'username' => $username,
                'storefront_id' => $this->getRequest()->get('storefront_id')
            ));
            $this->_redirect('rewardsinstore/index/login');
        }
        
        return $this;
    }
    
    public function logoutAction()
    {
        $this->logout();
        $this->_redirectUrl($this->getRequest()->getHeader('Referer'));
        return $this;
    }
    
    public function mainAction()
    {
        if ($this->getRequest()->has('storefront_id')) {
            $this->setStorefront($this->getRequest()->get('storefront_id'));
        }
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function fetchCustomersAction()
    {
        $this->keepAliveAction();
        
        $simplifiedCustomers = array();
        
        if ($this->getRequest()->has('term') && ($text = $this->getRequest()->getQuery('term'))) {
            try {
                $text = str_replace(" ", "%", $text);
                $storefront = Mage::getModel('rewardsinstore/storefront')->load(Mage::getSingleton('admin/session')->getStorefrontId());
                $customers = Mage::getModel('rewards/customer')->getCollection()
                    ->addNameToSelect()
                    ->addFieldToFilter('website_id', array('eq' => $storefront->getWebsiteId()))
                    ->addAttributeToFilter(array(
                        array('attribute' => 'name', 'like' => "%{$text}%"),
                        array('attribute' => 'email', 'like' => "%{$text}%")))
                    ->setPageSize(5);
                
                foreach ($customers as $customer) {
                    $rewardsCustomer = Mage::helper('rewardsinstore/customer')->getRewardsCustomer($customer);
                    $rewardsCustomer->loadCollections();
                    
                    $simplifiedCustomers[] = (object) array(
                        'name' => $customer->getName(),
                        'email' => $customer->getEmail(),
                        'points' => $rewardsCustomer->getUsablePointsBalance(1));
                }
                
                $this->getResponse()->setBody(Zend_Json::encode(array(
                    'success' => 1,
                    'customers' => $simplifiedCustomers
                )));
            } catch (Exception $ex) {
                Mage::helper('rewardsinstore')->logException($ex);
                $this->getResponse()->setBody(Zend_Json::encode(array(
                    'success' => 0,
                    'errorMsg' => $this->__("Could not retrieve list of customers. Please contact support.")
                )));
            }
            
            return $this;
        }
    }
    
    public function rewardCustomerAction()
    {
        $this->keepAliveAction();
        
        $this->getResponse()->setBody("");
        
        $email = $this->getRequest()->getQuery('email');
        $subtotal = $this->getRequest()->getQuery('subtotal');
        $storefrontId = Mage::getSingleton('admin/session')->getStorefrontId();
        
        // Validate values passed from webapp
        if (!$email) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 0,
                'errorMsg' => $this->__("Email address is invalid.")
            )));
            return $this;
        }
        
        $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($storefront->getWebsiteId())
            ->loadByEmail($email);
        
        if (!$customer->getId()) {
            Mage::helper('rewardsinstore')->log("The following customer was somehow selected, though apparently it doesn't exist (Email: {$email})");
            Mage::helper('rewardsinstore')->log(Mage::helper('rewards/debug')->getPrintableData($customer));
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 0,
                'errorMsg' => $this->__("Customer with email address {$email} does not exist.")
            )));
            return $this;
        }
        
        // TODO: validate subtotal to be greater than 0
        $data = array(
            'subtotal' => $subtotal
        );
        
        $passwd = Mage::helper('rewardsinstore/customer')->retrieveCustomerData($customer->getId());
        $customer->setPassword($passwd);
        
        $rewardsCustomer = Mage::helper('rewardsinstore/customer')->getRewardsCustomer($customer);
        
        try {
            $transfers = Mage::helper('rewardsinstore/pos')
                ->rewardCustomer($customer->getId(), $storefrontId, $data);
            
            $rewardsCustomer->loadCollections();
            Mage::helper('rewardsinstore/pos')->emailCustomer($rewardsCustomer);
            
            $result = $this->createAjaxResultFromTransfers($transfers);
            $result['success'] = 1;
            
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } catch (Exception $ex) {
            Mage::helper('rewardsinstore')->logException($ex);
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 0,
                'errorMsg' => $this->__("Failed to reward points to customer.")
            )));
            
            Mage::helper('rewardsinstore/pos')->emailCustomer($rewardsCustomer);
        }
        
        return $this;
    }
    
    public function createCustomerAction()
    {
        $this->keepAliveAction();
        
        try {
            $firstName = ucfirst($this->getRequest()->get('firstName'));
            $lastName = ucfirst($this->getRequest()->get('lastName'));
            $email = $this->getRequest()->get('email');
            
            $storefrontId = Mage::getSingleton('admin/session')->getStorefrontId();
            $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
            $website = Mage::getModel('core/website')->load($storefront->getWebsiteId());
            $store = $website->getDefaultStore();
            $group_id = Mage::getStoreConfig('rewardsinstore/customer_signup/customer_group');
            
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($website->getId())
                ->setStore($store)
                ->setIsCreatedInstore(true)
                ->setStorefrontId($storefrontId)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setGroupId($group_id)
                ->setPassword($customer->generatePassword(8))
                ->setRewardsinstoreCreationSource('webapp')
                ->save();
            
            if (!$customer->getId()) {
                Mage::helper('rewardsinstore')->log("Failed to create the following customer. (Email: {$email})");
                Mage::helper('rewardsinstore')->log(Mage::helper('rewards/debug')->getPrintableData($customer));
                $this->getResponse()->setBody(Zend_Json::encode(array(
                    'success' => 0,
                    'errorMsg' => $this->__("Failed to create customer.")
                )));
                return $this;
            }
            
            Mage::helper('rewardsinstore/customer')->stashCustomerData($customer);
            
            $transfers = Mage::helper('rewardsinstore/transfer')->getInstoreSignupTransfers($customer->getId());
            $result = $this->createAjaxResultFromTransfers($transfers);
            $result['success'] = 1;
            
            $this->getResponse()->setBody(Zend_Json::encode($result));
        } catch (Exception $ex) {
            Mage::helper('rewardsinstore')->logException($ex);
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 0,
                'errorMsg' => $this->__($ex->getMessage())
            )));
        }
        
        return $this;
    }
    
    public function setStorefrontAction()
    {
        $this->keepAliveAction();
        
        try {
            if (!$this->getRequest()->has('storefrontId')
                || !($storefront = $this->setStorefront($this->getRequest()->get('storefrontId')))) {
                    Mage::helper('rewardsinstore')->log("Cashier managed to select a non-storefront (or passed in null). Storefront ID: [". $this->getRequest()->get('storefrontId') ."]");
                    $this->getResponse()->setBody(Zend_Json::encode(array(
                        'success' => 0,
                        'errorMsg' => $this->__("Invalid storefront selected.")
                    )));
                    return $this;
            }
            
            Mage::dispatchEvent('rewardsinstore_storefront_login', array(
                'storefront_id' => $this->getRequest()->get('storefrontId')
            ));
            
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 1,
                'storefrontName' => $storefront->getName()
            )));
        } catch (Exception $ex) {
            Mage::helper('rewardsinstore')->logException($ex);
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => 0,
                'errorMsg' => $this->__("Storefront location does not exist.")
            )));
        }
        
        return $this;
    }
    
    public function keepAliveAction()
    {
        Mage::getSingleton('admin/session')->getCookie()->set(
            'rewardsinstore-key',
            Mage::getModel('adminhtml/url')->getSecretKey("webapp_ajax", "main")
        );
        
        return $this;
    }
    
    protected function setStorefront($storefrontId)
    {
        $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
        if ($storefront->getId()) {
            Mage::getSingleton('admin/session')->setStorefrontId($storefrontId);
            return $storefront;
        }
        
        return null;
    }
    
    protected function createAjaxResultFromTransfers($transfers)
    {
        // Initialize result structure
        $result = array(
            'transfers' => array(),
            'points' => 0
        );
        
        foreach ($transfers as $transfer) {
            array_push($result['transfers'],
                Mage::helper('rewardsinstore/transfer')->getTransferSummary($transfer));
            
            $result['points'] += $transfer->getQuantity();
        }
        
        return $result;
    }
    
    protected function logout()
    {
        $session = Mage::getSingleton('admin/session')->unsetAll();
        $session->getCookie()->delete($session->getSessionName());
        $session->getCookie()->delete('rewardsinstore-key');
    }
}
