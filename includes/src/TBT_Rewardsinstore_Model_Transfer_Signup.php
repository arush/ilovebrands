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
class TBT_Rewardsinstore_Model_Transfer_Signup extends TBT_Rewards_Model_Transfer
{
    const REFERENCE_TYPE = TBT_Rewardsinstore_Model_Transfer_Reference_Signup::REFERENCE_TYPE_ID;
    
    public function getTransfersAssociatedWithSignup($customer_id)
    {
        return $this->getCollection()
                ->addFilter('reference_type', self::REFERENCE_TYPE)
                ->addFilter('reference_id', $customer_id);
    }
    
    public function setCustomerId($id)
    {
        parent::setCustomerId($id);
        $this->clearReferences();
        $this->setReferenceType(self::REFERENCE_TYPE);
        $this->setReferenceId($id);
        $this->_data['customer_id'] = $id;
        return $this;
    }
    
    public function create($customer, $rule)
    {
        $num_points = $rule->getPointsAmount();
        $currency_id = $rule->getPointsCurrencyId();
        $rule_id = $rule->getId();
        $transfer = $this->initTransfer($num_points, $currency_id, $rule_id);
        $store = $customer->getStore();
        
        if (!$transfer) {
            return false;
        }
        
        //get the default starting status
        $initial_status = Mage::getStoreConfig('rewards/InitialTransferStatus/AfterInstoreSignup', $store);
        if (!$transfer->setStatus(null, $initial_status)) {
            return false;
        }
        
        // Translate the message through the core translation engine (nto the store view system) in case people want to use that instead
        // This is not normal, but we found that a lot of people preferred to use the standard translation system insteaed of the 
        // store view system so this lets them use both.
        $initial_transfer_msg = Mage::getStoreConfig('rewards/transferComments/instoreSignup', $store);
        $comments = Mage::helper('rewards')->__($initial_transfer_msg);
        
        $this->setComments($comments)->setCustomerId($customer->getId())->save();
        
        return true;
    }
}
