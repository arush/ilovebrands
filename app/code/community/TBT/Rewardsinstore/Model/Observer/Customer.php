<?php

class TBT_Rewardsinstore_Model_Observer_Customer extends Varien_Object
{
    public function __construct() { }
    
    public function beforeSave($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($customer->getIsCreatedInstore() || $customer->getStorefrontId()) {
            if (!$customer->getIsCreatedInstore()) {
                $customer->setIsCreatedInstore(true);
            }
            
            if (!$customer->getId()) {
                $source = $customer->getRewardsinstoreCreationSource() ?
                    $customer->getRewardsinstoreCreationSource() :
                    'api';
                
                Mage::dispatchEvent('rewardsinstore_customer_signup_before', array(
                    'customer' => $customer,
                    'source'   => $source
                ));
            }
        }
        
        return $this;
    }
    
    public function afterSave($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($customer->getIsCreatedInstore()) {
            if ($customer->getCreatedAt() == $customer->getUpdatedAt()) {
                $source = $customer->getRewardsinstoreCreationSource() ?
                    $customer->getRewardsinstoreCreationSource() :
                    'api';
                
                Mage::dispatchEvent('rewardsinstore_customer_signup_after', array(
                    'customer' => $customer,
                    'source'   => $source
                ));
            }
        }
        
        return $this;
    }
}
