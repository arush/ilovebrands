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
 * Instore Points Transfer API
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Api_Transfer extends TBT_Rewardsinstore_Model_Api_Abstract
{
    protected $_ignoredParams = array(
        'description', 'created_at', 'updated_at',
    );
    
    protected $_ignoredOutput = array(
        'effective_start', 'expire_date', 
        'rewards_transfer_reference_id',
    );
    
    protected $_mapAttributes = array(
        'transfer_id' => 'rewards_transfer_id',
        'created_at' => 'creation_ts',
        'updated_at' => 'last_update_ts'
    );
    
    // TODO: move these to mapAttributes and only ever map attributes at end through a function
    protected $_mapFilters = array (
        'created_before' => 'creation_ts',
        'created_after' => 'creation_ts' 
    );
    
    protected $_dateFilters = array (
        'created_after' => 'from',
        'created_before' => 'to');
    
    protected $_tableFilters = array (
        'storefront_id' => 'instore_quote'
    );
    
    protected $_transferStatusCodes = array(
        'CANCELLED'        => 1,
        'PENDING_APPROVAL' => 3,
        'PENDING_EVENT'    => 4,
        'APPROVED'         => 5,
        'PENDING_TIME'     => 6
    );
    
    public function info($transferId, $attributes = array())
    {
        if (!$transferId) {
            $this->_fault('data_invalid', $this->__("You must supply the ID of the points transfer to request."));
        }
        
        if (!is_array($attributes)) {
            $this->_fault('data_invalid', $this->__("The 'attributes' field must be an array."));
        }
        
        try {
            $transfer = Mage::getModel('rewards/transfer')->load($transferId);
            if (!$transfer->getId()) {
                $this->_fault('not_exists', $this->__("Points Transfer with ID={$transferId} does not exist."));
            }
            
            return $this->_prepareEntity($transfer, $attributes);
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
    }
    
    public function items($filters)
    {
        $filters = $this->_prepareInput($filters);
        
        if (isset($filters['status'])) {
            $filters['status'] = $this->_getTransferStatusId($filters['status']);
        }
        
        try {
            $collection = Mage::getModel('rewards/transfer')->getCollection();
            
            $collection->getSelect()
                ->join( array('transfer_reference' => $collection->getTable('rewards/transfer_reference')), 
                    'transfer_reference.rewards_transfer_id = main_table.rewards_transfer_id', 
                    array('*'));
                    
            $collection->getSelect()
                ->join( array('quote' => $collection->getTable('sales/quote')), 
                    'quote.entity_id = transfer_reference.reference_id', 
                    array());
            
            // Instore quote type
            $collection->addFieldToFilter('transfer_reference.reference_type', 50);
            
            $collection->getSelect()
                ->join( array('instore_quote' => $collection->getTable('rewardsinstore/quote')), 
                    'instore_quote.quote_id = quote.entity_id', 
                    array('*'));
                    
            $collection = $this->_applyFilters($collection, $filters);
            return $this->_prepareCollection($collection);
        } catch (Exception $ex) {
            $this->_fault('filters_invalid', $ex->getMessage());
        }
        
        return null;
    }
    
    protected function _getTransferStatusId($code)
    {
        if (isset($this->_transferStatusCodes[$code])) {
            return $this->_transferStatusCodes[$code];
        }
        
        return null;
    }
}
