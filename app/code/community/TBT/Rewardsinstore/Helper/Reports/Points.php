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
 * Provides helper methods for getting analytics data about Instore points
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Helper_Reports_Points extends Mage_Core_Helper_Abstract
{
    /**
     * Calculates the total number of points distributed instore.
     *
     * @param int|array $storefrontId The storefront(s) by which to filter
     * @return int total
     */
    public function getTotalPointsDistributed($storefrontIds = array())
    {
        if (!is_array($storefrontIds)) {
            $storefrontIds = array($storefrontIds);
        }
        $storefrontIds = array_filter($storefrontIds);
        
        $transfers = Mage::getModel('rewards/transfer')->getCollection()
            ->addFieldToFilter('reason_id', 1)
            ->sumPoints()
            ->addFieldToFilter('reference_type', array(
                'orders'  => TBT_Rewardsinstore_Model_Transfer_Reference_Quote::REFERENCE_TYPE_ID,
                'signups' => TBT_Rewardsinstore_Model_Transfer_Reference_Signup::REFERENCE_TYPE_ID));
        
        if (!empty($storefrontIds)) {
            $transfers->getSelect()->joinLeft('rewardsinstore_quote',
                'rewardsinstore_quote.quote_id = reference_table.reference_id',
                array('rewardsinstore_quote.storefront_id'));
            
            // TODO: let's use an IN condition for this
            foreach ($storefrontIds as $storefrontId) {
                $transfers->getSelect()->orWhere('storefront_id = ?', $storefrontId);
            }
        }
            
        return (int)$transfers->load()->getFirstItem()->getPointsCount();
    }
    
    public function getAveragePointsPerOrder($storefrontIds = array())
    {
        if (!is_array($storefrontIds)) {
            $storefrontIds = array($storefrontIds);
        }
        $storefrontIds = array_filter($storefrontIds);
        
        $transfers = Mage::getResourceModel('rewards/transfer_collection')
            ->addFieldToFilter('reason_id', 1)
            ->addFieldToFilter('reference_type', TBT_Rewardsinstore_Model_Transfer_Reference_Quote::REFERENCE_TYPE_ID);
        $transfers->getSelect()
            ->group('reference_id')
            ->columns(array('points_count' => "SUM(`quantity`)"));
        
        if (!empty($storefrontIds)) {
            $transfers->getSelect()->joinLeft('rewardsinstore_quote',
                'rewardsinstore_quote.quote_id = reference_table.reference_id',
                array('rewardsinstore_quote.storefront_id'));
            
            // TODO: let's use an IN condition for this
            foreach ($storefrontIds as $storefrontId) {
                $transfers->getSelect()->orWhere('storefront_id = ?', $storefrontId);
            }
        }
        
        if (count($transfers) == 0) {
            return 0;
        }
        
        // calculate the average points per order, seeing as we can't call AVG( SUM(x) ) in SQL
        $count = count($transfers);
        $total = 0;
        foreach ($transfers as $tx) {
            $total += $tx->getPointsCount();
        }
        
        return ($total / $count);
    }
}
