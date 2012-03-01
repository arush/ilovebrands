<?php
class TBT_Rewardsinstore_Model_Cart_Api extends TBT_Rewardsinstore_Model_Api_Abstract
{
    public function order($quoteId, $storefrontId)
    {
        $quote = Mage::getModel('rewardsinstore/quote')->load($quoteId);
        if (!$quote->getId()) {
            $this->_fault('data_invalid', $this->__("Cart with ID={$quoteId} does not exist."));
        }
        
        $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
        if (!$storefront->getId()) {
            $this->_fault('data_invalid', $this->__("Storefront with ID={$storefrontId} does not exist."));
        }
        
        try {
            $quote->setStorefrontId($storefrontId)
                ->save();
            
            $transfers = $quote->placeInstoreQuote()
                ->getAssociatedTransfers();
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
        
        return $transfers->getData();
    }
}
