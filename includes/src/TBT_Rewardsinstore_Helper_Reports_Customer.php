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
 * Provides helper methods for getting analytics data about Instore customers
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Helper_Reports_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Calculates the total number customers created instore.
     *
     * @param int $storefrontId
     * @return int
     */
    public function getTotalInstoreCustomers($storefrontId = null)
    {
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('is_created_instore', 1);
        
        if ($storefrontId) {
            $customers->addFieldToFilter('storefront_id', $storefrontId);
        }
        
        return count($customers);
    }
    
    /**
     * Calculates the total number of customers created instore who have signed in online.
     * 
     * @param int $storefrontId
     * @return int
     */
    public function getActiveInstoreCustomers($storefrontId = null)
    {
        // TODO: optimize this with similar joins to Mage_Log_Model_Mysql4_Customer
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('is_created_instore', 1);
        
        if ($storefrontId) {
            $customers->addFieldToFilter('storefront_id', $storefrontId);
        }
        
        $customerIds = array_unique($customers->getAllIds());
        
        $activeCount = 0;
        
        Mage::log('cid: ' . implode(',', $customerIds));
        
        foreach ($customerIds as $id) {
            $activeCount += Mage::getModel('log/customer')->load($id)->getLoginAtTimestamp() ? 1 : 0;
        }
        
        return $activeCount;
    }
    
    /**
     * Total number of customers who have been rewarded instore
     *
     * @param int $storefrontId
     * @return int
     */
    public function getTotalInstoreParticipants($storefrontId = null)
    {
        $collection = Mage::getResourceModel('rewardsinstore/quote_collection');
        $collection->getSelect()->group('customer_id');
        
        if ($storefrontId) {
            $collection->addFieldToFilter('storefront_id', $storefrontId);
        }
        
        return count($collection);
    }
    
    /**
     * Number of customers who have been rewarded twice instore
     * (shows increased loyalty)
     *
     * @param int $storefrontId
     * @return int
     */
    public function getRepeatInstoreCustomers($storefrontId = null)
    {
        $collection = Mage::getResourceModel('rewardsinstore/quote_collection')
            ->addExpressionFieldToSelect('quote_count', 'COUNT({{quote_id}})', 'quote_id');
        $collection->getSelect()
            ->group('customer_id')
            ->having('quote_count > 1');
        
        if ($storefrontId) {
            $collection->addFieldToFilter('storefront_id', $storefrontId);
        }
        
        return count($collection);
    }
    
    /**
     * Number of instore customers who have made their first online purchase
     * (shows sales which never would have been made)
     *
     * @param int $storefrontId
     * @return int
     */
    public function getWebPurchasingInstoreCustomers($storefrontId = null)
    {
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addFieldToFilter('is_created_instore', 1);
        // TODO: check out $eav->joinField or $eav->joinTable
        $customers->getSelect()->joinInner('sales_flat_order',
            'sales_flat_order.customer_id = e.entity_id');
        
        if ($storefrontId) {
            $customers->addFieldToFilter('storefront_id', $storefrontId);
        }
        
        return count($customers);
    }
}
