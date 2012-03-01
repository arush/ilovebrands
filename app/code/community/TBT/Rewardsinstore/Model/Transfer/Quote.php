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
class TBT_Rewardsinstore_Model_Transfer_Quote extends TBT_Rewards_Model_Transfer
{
    const REFERENCE_QUOTE = TBT_Rewardsinstore_Model_Transfer_Reference_Quote::REFERENCE_TYPE_ID;
    
    /**
     * TODO: this function should be abstracted to getAssociatedTransfers() in a parent class
     * which will use the REFERENCE_QUOTE (to be changed to REFERENCE_ID) which will be set
     * in this child class's constructor.
     * Further refactoring will involve changing setQuoteId to setReferenceId.
     *
     * This refactoring needs a ton of testing to it's to be done at an appropriate time.
     */
    public function getTransfersAssociatedWithQuote($quote_id)
    {
        return $this->getCollection()
                ->addFilter('reference_type', self::REFERENCE_QUOTE)
                ->addFilter('reference_id', $quote_id);
    }
    
    public function setQuoteId($id)
    {
        $this->clearReferences();
        $this->setReferenceType(self::REFERENCE_QUOTE);
        $this->setReferenceId($id);
        $this->_data['quote_id'] = $id;
        return $this;
    }
    
    public function create($num_points, $currency_id, $customerId, $quoteId, 
            $comment = "Points received for making an Instore purchase", 
            $reason_id = TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_DISTRIBUTION, // TODO: use new reason (careful, this makes transfers not show up in grid)
            $transferStatus = TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)
    {
                
        // ALWAYS ensure that we only give an integral amount of points
        $num_points = floor($num_points);
        if ($num_points <= 0) {
            return $this;
        }
        
        $this->setQuoteId($quoteId);
        $this->setReasonId($reason_id);
        if (!$this->setStatus(null, $transferStatus)) {
            return $this;
        }
        
        $this->setId(null)
            ->setCreationTs(now())
            ->setLastUpdateTs(now())
            ->setCurrencyId($currency_id)
            ->setQuantity($num_points)
            ->setComments($comment)
            ->setCustomerId($customerId)
            ->save();
            
        return $this;
    }
}
