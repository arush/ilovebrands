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
 * The Rewards Customer must be observed so we can cancel it from rewarding new signups
 * when they are Instore signups.
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Observer_Rewards_Customer extends Varien_Object
{
    public function rewardsCustomerSignup($o)
    {
        $event = $o->getEvent();
        $customer = $event->getCustomer();
        $result = $event->getResult();
        
        // return if not an instore customer
        if (!$customer->getIsCreatedInstore()) {
            return $this;
        }
        
        // Cancel the Rewards module from rewarding on signup
        $result->setIsValid(false);
        
        $this->rewardInstoreCustomerSignup($customer);
        
        return $this;
    }
    
    public function log($msg)
    {
        Mage::helper('rewardsinstore')->log($msg);
    }
    
    /**
     * Loops through each Special rule. If the rule applies, create a new transfer.
     */
    public function rewardInstoreCustomerSignup($customer)
    {
        $ruleCollection = $this->getApplicableRules($customer);
        
        foreach ($ruleCollection as $rule) {
            if (!$rule->getId()) {
                continue;
            }
            try {
                $transfer = Mage::getModel('rewardsinstore/transfer_signup');
                $is_transfer_successful = $transfer->create($customer, $rule);
                
                if ($is_transfer_successful) {
                    $points_for_signup = Mage::getModel('rewards/points')->set($rule);
                }
            } catch (Exception $ex) {
                Mage::logException($ex);
                /* TODO: find a way to return this error to the webapp (simply re-throwing the exception
                 * would make it look like the customer failed to be created, which is false */
            }
        }
    }
    
    protected function getApplicableRules($customer)
    {
        /*
         * TODO: Refactor this for other behaviour rules
         * 
         * The TBT_Rewards_Model_Special_Validator checks that
         * the current website matches that in the rule conditions.
         * However when we create customers from the webapp,
         * the website is the admin store, so no rules pass.
         * As a temporary solution we set the current store as that
         * of where the customer was created, then set it back to the
         * original after we get the valid rules.
         */
        $orig_store = Mage::app()->getStore()->getCode();
        Mage::app()->setCurrentStore($customer->getStore()->getCode());
        
        /* TODO: This also needs to be refactored.
         * Sweet Tooth checks if the rules validate against the customer in
         * the session, so if we want to know if they are TRULY valid we need
         * to temporarily place our customer in the session and put the original
         * back in when we're done.
         */
        $orig_customer = Mage::getSingleton('rewards/session')->getSessionCustomer();
        Mage::getSingleton('rewards/session')->setCustomer($customer);
        
        // Get all valid behaviour rules for the current customer and context
        $rules = Mage::getModel('rewards/special_validator')
            ->getApplicableRules(TBT_Rewardsinstore_Model_Special_Signup::ACTION_CODE);
        
        // Restore original store
        Mage::app()->setCurrentStore($orig_store);
        
        // Restore original customer
        Mage::getSingleton('rewards/session')->setCustomer($orig_customer);
        
        return $rules;
    }
}
