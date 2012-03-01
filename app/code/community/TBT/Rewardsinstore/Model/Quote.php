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
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Quote extends TBT_Rewards_Model_Sales_Quote
{
    protected $_eventPrefix = 'rewardsinstore_quote';
    protected $_eventObject = 'quote';
    
    protected $_areQuotesPolluted = false;
    
    // Reference Id for Instore Quotes
    protected $_transferReference;
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('rewardsinstore/quote');
        $this->_transferReference = TBT_Rewardsinstore_Model_Transfer_Reference_Quote::REFERENCE_TYPE_ID;
        $this->setIsInstore(true);
    }
    
    /**
     * Sets the storefrontId and its default
     * store view to the quote.
     *
     * @param int $storefrontId
     */
    public function setStorefrontId($storefrontId)
    {
        $this->_setStoreIdFromStorefrontId($storefrontId);
        return $this->setData('storefront_id', $storefrontId);
    }
    
    /**
     * Sets the store view for this quote based on
     * the default store view of the storefront
     *
     * @param int $storefrontId
     * @return TBT_Rewardsinstore_Model_Quote $this
     */
    protected function _setStoreIdFromStorefrontId($storefrontId)
    {
        $store = Mage::getModel('rewardsinstore/storefront')
            ->load($storefrontId)
            ->getDefaultStore();
        
        if ($store) {
            $this->setStoreId($store->getId());
        } else {
            throw new Exception('Cannot find the default store view for storefront with id: ' . $storefrontId);
        }
        
        return $this;
    }
    
    // TODO: Refactor this function to be in a Model_Convert_Quote class like they do in the sales module
    public function placeInstoreQuote ()
    {
        // TODO: refactor everything to do with points calculations out of the session and into a new Totals model
        $session = Mage::getSingleton('rewards/session');
        $all_cart_points = $session->updateShoppingCartPoints($this);
        
        // Create transfer for each set of points awarded to this cart
        foreach ($all_cart_points as $cart_points) {
        
            $amount = $cart_points['amount'];
            $currency = $cart_points['currency'];
            $rule_id = $cart_points['rule_id'];
            
            $transfer = Mage::getModel('rewardsinstore/transfer_quote');
            
            // TODO: create config for initial quote status
            $transferStatus = Mage::helper('rewards/config')->getInitialTransferStatusAfterOrder();
            
            // TODO: make transfer comments configurable
            
            $transfer->create($amount, $currency, $this->getCustomerId(), $this->getId());
        }
        
        // Deactivate quote so it doesn't show up in the customer's online cart
        $this->setIsActive(false)
            ->save();
        
        // TODO: look into setting the checkout_method and applied_rules attributes here
        
        return $this;
    }
    
    // TODO: refactor this to be in a new TBT_Rewards_Model_Transfer_Referenceable interface
    public function getAssociatedTransfers()
    {
        return Mage::getModel('rewards/transfer')->getCollection()
            ->addFilter ('reference_type', $this->_transferReference)
            ->addFilter ('reference_id', $this->getId());
    }
    
    /**
     * Allows non-Instore quotes to be loaded into this model
     *
     * @param bool $flag
     * @return TBT_Rewardsinstore_Model_Quote
     */
    public function polluteQuotes($flag = true)
    {
        $this->_areQuotesPolluted = $flag;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getQuotePollution()
    {
        return $this->_areQuotesPolluted;
    }
    
    /**
     * Save object data.
     * Specifically, only save the Instore-specific data and salesquote reference.
     *
     * @return TBT_Rewardsinstore_Model_Quote
     */
    public function save()
    {
        $base_quote = Mage::getModel('sales/quote')
            ->setData($this->getData())
            ->setId($this->getId())
            ->save();
        
        if (is_null($this->getId())) {
            $this->isObjectNew(true);
        }
        
        $this->setId($base_quote->getId());
        $this->setEntityId($base_quote->getId());
        foreach ($this->getAddressesCollection() as $address) {
            $address->setQuote($base_quote);
        }
        foreach ($this->getItemsCollection() as $item) {
            $item->setQuote($base_quote);
        }
        foreach ($this->getPaymentsCollection() as $payment) {
            $payment->setQuote($base_quote);
        }
        
        // TODO: necessary for quotes?
        $this->setForceUpdateGridRecords(true);
        parent::save();
        
        $this->setId($this->getEntityId());
        
        return $this;
    }
    
    /**
     * Loads quote data (Instore quote combined with base quote)
     *
     * @return TBT_Rewardsinstore_Model_Quote
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        
        if(!$this->getId()) {
            $this->setId($this->getEntityId())
                ->setIsInstore(false);
        }
        
        return $this;
    }
    
    /**
     * Delete Instore quote and associated salesquote from database
     *
     * @return TBT_Rewardsinstore_Model_Quote
     */
    public function delete()
    {
        $quote_id = $this->getId();
        
        parent::delete();
        
        // Delete base order right after our Instore order is deleted
        Mage::getModel('sales/quote')
            ->load($quote_id)
            ->delete();
        
        return $this;
    }
}
