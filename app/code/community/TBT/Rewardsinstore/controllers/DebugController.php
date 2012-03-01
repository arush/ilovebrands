<?php
require_once("app/code/community/TBT/Rewards/controllers/Debug/AbstractController.php");
class TBT_Rewardsinstore_DebugController extends TBT_Rewards_Debug_AbstractController
{
    public function instoreCustomerAction()
    {
        $id = $this->getRequest()->has('id') ? $this->getRequest()->get('id') : 5;
        $newIsInstore = $this->getRequest()->has('isInstore') ? $this->getRequest()->get('isInstore') : true;
        $newStorefrontId = $this->getRequest()->has('storefrontId') ? $this->getRequest()->get('storefrontId') : 3;
        
        $customer = Mage::getModel('customer/customer');
        $customer->load($id);
        
        echo "Customer: ". $customer->getName();
        
        echo "\n\nPre-Changes:";
        echo "\n\tIs Created Instore?: ". ($customer->getIsCreatedInstore() ? "yes" : "no");
        //if ($customer->getIsCreatedInstore()) {
            echo "\n\tStorefront: ". Mage::getModel('rewardsinstore/storefront')->load($customer->getStorefrontId())->getName();
        //}
        
        echo "\n\nChanged:";
        $customer->setIsCreatedInstore($newIsInstore)
            ->setStorefrontId($newStorefrontId)
            ->save();
        echo "\n\tExpectedChanges: {";
        echo "\n\t\tis_instore: '$newIsInstore',";
        echo "\n\t\tstorefront_id: '$newStorefrontId'";
        echo "\n\t},";
        echo "\n\tActualChanges: {";
        echo "\n\t\tis_instore: '{$customer->getIsCreatedInstore()}',";
        echo "\n\t\tstorefront_id: '{$customer->getStorefrontId()}'";
        echo "\n\t}";
        
        echo "\n\nReloaded:";
        $customer = Mage::getModel('customer/customer')->load($id);
        echo "\n\tIs Created Instore?: ". ($customer->getIsCreatedInstore() ? "yes" : "no");
        if ($customer->getIsCreatedInstore()) {
            echo "\n\tStorefront: ". Mage::getModel('rewardsinstore/storefront')->load($customer->getStorefrontId())->getName();
        }
        
        return $this;
    }
    
    public function cronAction()
    {
        $cron = Mage::getModel('rewardsinstore/observer_cron');
        $cron->emailCustomers(1);
        
        return $this;
    }
    
    public function customerAction()
    {
        $cx = Mage::getModel('customer/customer')->load(2);
        echo "<pre>Loaded:\n". print_r($cx->getData(), true) ."</pre>";
        $cx->setTelephone("519-400-1891");
        $cx->save();
        
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('telephone')
            ->addAttributeToFilter('entity_id', 2);
        foreach($customers as $customer) {
            echo "<pre>" . print_r($customer->getData(), true) ."</pre>";
        }
        
        return $this;
    }
    
    public function quoteAction()
    {
        $email = "nelkaake@wdca.ca";
        $subtotal = 100;
        $storefrontId = 1;
        
        $data = array('subtotal' => $subtotal);
        $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($storefront->getWebsiteId())
            ->loadByEmail($email);
        
        Mage::helper('rewardsinstore/pos')->rewardCustomer($customer->getId(), $storefrontId, $data);
        
        //$quote = Mage::getModel('rewardsinstore/quote')->load(57);
        //print_r($quote->getData());
        
        //$quotes = Mage::getModel('rewardsinstore/quote')->getCollection();
        //foreach ($quotes as $quote) {
        //    print_r($quote->getData());
        //}
    }
    
    public function emailAction()
    {
        echo Mage::getStoreConfig ( "rewardsinstore/email_templates/welcome", Mage::app()->getDefaultStoreView()->getId() );
    }
}
