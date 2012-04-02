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
 * Used to observe random events from Sweet Tooth
 * TODO: Change this to TBT_Rewardsinstore_Model_Observer_Rewards
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Rewards_Observer extends Varien_Object
{
    /**
     * This function does one of two things depending on the type of order:
     *
     * Online order/quote:
     * - clears result for instore rules
     *
     * Instore order/quote:
     * - clears result for online rules
     * - validates instore rules agains the order/quote
     * 
     * Sweet Tooth Core handles the points transfers for now.
     *
     * @param unknown_type $o
     * @return $this
     */
    public function calculateCartPoints($o)
    {
        $event = $o->getEvent();
        
        $rule_id = $event->getRuleId();
        $order_items = $event->getOrderItems();
        $result = $event->getResult();
        
        if (!$item = $this->getValidItem($order_items)) {
            return $this;
        }
        
        if ($storefront = $this->getStorefrontIdFromItem($item)) {
            
            /**
             * We're dealing with an instore order or quote so clear
             * all online rules, and validate all instore rules.
             */
            
            if ($instore_rule = Mage::helper('rewardsinstore/rule')->isInstoreRule($rule_id)) {
                $this->validateInstoreRule($instore_rule, $storefront, $result);
            } else {
                $this->clearPointsFromResult($result);
            }
            
        } else {
            
            /**
             * We're dealing with an online order or quote so clear
             * all instore rules, and let the web rules pass through.
             */
            
            if ($instore_rule = Mage::helper('rewardsinstore/rule')->isInstoreRule($rule_id)) {
                $this->clearPointsFromResult($result);
            }
        }
        
        return $this;
    
    }
    
    /**
     * Checks whether the item is part of an instore quote or order
     */
    protected function getStorefrontIdFromItem($item)
    {
        $quote = $item->getQuote();
        
        if ($quote && $quote->getIsInstore()) {
            // TODO: find a better place to clear these points (observer?)
            Mage::helper('rewardsinstore/points')->clearEarnedPointsOnItems($quote->getAllItems());
            return $quote->getStorefrontId();
        }
        
        return null;
    }
    
    protected function getValidItem($order_items)
    {
        if (empty($order_items) || !is_array($order_items)) {
            return null;
        }
        return $order_items[0];
    }
    
    protected function validateInstoreRule($rule, $storefront, $result)
    {
        $rule_storefronts = $rule->getStorefrontIds();
        
        // Do not apply rule if storefronts don't match up
        if (!in_array($storefront, explode(',', $rule_storefronts))) {
            $this->log('Rule ' . $rule->getId() . ' not applied. Wrong Storefront');
            $this->clearPointsFromResult($result);
        } else {
            $this->log('Rule ' . $rule->getId() . ' applied for correct storefront');
        }
    }
    
    protected function clearPointsFromResult($result)
    {
        $result->setPointsToTransfer(0);
    }
    
    public function log($msg)
    {
        Mage::helper('rewardsinstore')->log($msg);
    }
}
