<?php
//Mage_Adminhtml_Controller_Action
//Mage_Core_Controller_Front_Action
class TBT_Rewardsinstore_WebappController extends Mage_Core_Controller_Front_Action
{

    protected function _construct()
    {
        parent::_construct();
        $this->_usedModuleName = "rewardsinstore";
    }

    public function indexAction()
    {
        if (Mage::getConfig()->getModuleConfig('TBT_Rewards')->is('active', 'false')) {
            throw new Exception(Mage::helper('rewardsinstore')->__("Sweet Tooth must be installed in order to use Sweet Tooth Instore"));
        }
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function loginAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function fetchCustomersAction()
    {
        $simplifiedCustomers = array();
        
        // TODO - is this not working right because we're not running under rewardsinstoreadmin?
        //if (Mage::getSingleton('admin/session')->isLoggedIn()) {
        if ($this->getRequest()->has('term') && ($text = $this->getRequest()->getQuery('term'))) {
            $customers = Mage::getModel('customer/customer')->getCollection()
                ->addNameToSelect()->addAttributeToFilter('name', array('like' => "%{$text}%"))
                //->where storefront is $storefrontId
                ->setPageSize(5);
                
            foreach ($customers as $customer) {
                $simplifiedCustomers[] = (object) array(
                    'label' => $customer->getName(),
                    'full_name' => $customer->getName(),
                    'email' => $customer->getEmail());
            }
        }
        //}
        
        $this->getResponse()->setBody(Zend_Json::encode($simplifiedCustomers));
    }
    
    public function estimatePointsAction()
    {
        // error 401: Unauthorized
        // TODO - if user has not logged in, return a 401 error
        
        $email = $this->getRequest()->getQuery('email');
        $subtotal = $this->getRequest()->getQuery('subtotal');
        
        // TODO - calculate estimated points
        $estimatedPoints = array('points' => 225);
        
        $this->getResponse()->setBody(Zend_Json::encode($estimatedPoints));
    }
    
    public function rewardCustomerAction()
    {
        // error 401: Unauthorized
        // TODO - if user has not logged in, return a 401 error
        
        $email = $this->getRequest()->getPost('email');
        $subtotal = $this->getRequest()->getPost('subtotal');
        
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email);
        
        $this->getResponse()->setBody("");
        if (!$subtotal || !$customer->getId()) {
            // error 403: Forbidden
            $this->getResponse()->setHttpResponseCode(403);
        }
    }
    
    public function createCustomerAction()
    {
        $firstName = $this->getRequest()->get('firstName');
        $lastName = $this->getRequest()->get('lastName');
        $email = $this->getRequest()->get('email');
        
        Mage::log("First:{$firstName}, Last:{$lastName}, Email:{$email}");
        
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getWebsite()->getDefaultStore();
        
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email)
            ->setPassword($customer->generatePassword(8))
            ->save();
        $customer->sendPasswordReminderEmail();
    }
}
