<?php
class TBT_Rewardsinstore_Model_Transfer_Api extends TBT_Rewardsinstore_Model_Api_Abstract
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
