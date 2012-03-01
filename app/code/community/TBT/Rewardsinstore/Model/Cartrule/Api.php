<?php
class TBT_Rewardsinstore_Model_Cartrule_Api extends TBT_Rewardsinstore_Model_Api_Abstract
{
    protected $_ignoredParams = array(
        'code', 'discount_amount', 'discount_qty', 'discount_step'
    );
    
    protected $_ignoredOutput = array(
        'code', 'discount_amount', 'discount_qty', 'discount_step'
    );
    
    protected $_mapAttributes = array(
        'points_rule_id' => 'rule_id',
        'enabled' => 'is_active');
    
    protected $_mapFilters = array (
        'starts_after' => 'from_date',
        'starts_before' => 'from_date',
        'ends_after' => 'to_date',
        'ends_before' => 'to_date'
    );
    
    protected $_dateFilters = array (
        'starts_after' => 'from',
        'starts_before' => 'to',
        'ends_after' => 'from',
        'ends_before' => 'to');
    
    protected $_arrayFilters = array (
        'storefront_ids', 'website_ids'
    );
    
    protected $_booleans = array(
        'is_active');
    
    public function info($ruleId, $attributes = array())
    {
        if (!$ruleId) {
            $this->_fault('data_invalid', $this->__("You must supply the ID of the points rule to request."));
        }
        
        if (!is_array($attributes)) {
            $this->_fault('data_invalid', $this->__("The 'attributes' field must be an array."));
        }
        
        try {
            $cartrule = Mage::getModel('rewardsinstore/cartrule')->load($ruleId);
            if (!$cartrule->getId()) {
                $this->_fault('not_exists', $this->__("Points Rule with ID={$ruleId} does not exist."));
            }
            
            return $this->_prepareEntity($cartrule, $attributes);
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
    }
    
    public function items($filters)
    {
        $filters = $this->_prepareInput($filters);
        $filters = $this->_cleanBooleanInput($filters);
        
        try {
            $collection = Mage::getModel('rewardsinstore/cartrule')->getCollection();
            $collection = $this->_applyFilters($collection, $filters);
            return $this->_prepareCollection($collection);
        } catch (Exception $ex) {
            $this->_fault('filters_invalid', $ex->getMessage());
        }
    }
}
