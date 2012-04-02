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
 * Instore Cart Rule API
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Api_Cartrule extends TBT_Rewardsinstore_Model_Api_Abstract
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
