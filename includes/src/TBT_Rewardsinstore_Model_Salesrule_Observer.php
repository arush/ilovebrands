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
 * TODO: Change this to TBT_Rewardsinstore_Model_Observer_Salesrule
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Salesrule_Observer extends Varien_Object
{
    /**
     * This method runs for every valid rule for the quote.
     * If the order is an instore order, we clear any discounts 
     * so they are not applied.
     * 
     * @param Varien_Object $o
     */
    public function salesruleValidatorProcess($o)
    {
        $event = $o->getEvent ();
        
        $rule = $event->getRule ();
        $item = $event->getItem ();
        $address = $event->getAddress ();
        $quote = $event->getQuote ();
        $qty = $event->getQty();
        $result = $event->getResult();
        
        $rule_id = $rule->getId ();
        
        try {
            
            $this->log('Processing Rule ' . $rule_id . ' with discountAmount: ' .
                $result->getDiscountAmount());
                
            if ($quote->getIsInstore()) {
                
                $this->log('instore quote.');
                $this->log('Before reset: ' . $this->resultToString($result));
                
                $this->resetResult($result);
                
                $this->log('After reset: ' . $this->resultToString($result));
                
            } else {
                $this->log('web quote.');
                $this->log('Result: ' . $this->resultToString($result));
            }
        
        } catch (Exception $e) {
            Mage::log("Exception in Insore observer: " . $e->getMessage());
            Mage::logException($e);
        }
        
        return $this;
    }
    
    protected function resetResult($result)
    {
        $result->setBaseDiscountAmount(0);
        $result->setDiscountAmount(0);
    }
    
    public function resultToString($result)
    {
        return 'Base: ' . $result->getBaseDiscountAmount() .
           ' Actual: ' . $result->getDiscountAmount();
    }
    
    public function log($msg)
    {
        Mage::helper('rewardsinstore')->log($msg);
    }
}
