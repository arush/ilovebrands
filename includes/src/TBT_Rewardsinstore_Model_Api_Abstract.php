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
 * Abstract class for all Instore API resources
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
abstract class TBT_Rewardsinstore_Model_Api_Abstract extends Mage_Api_Model_Resource_Abstract
{
    
    /**
     * Parameters to be ignored on input
     *
     * @var array
     */
    protected $_ignoredParams = array();
    
    /**
     * Attributes to be omitted in output
     *
     * @var array
     */
    protected $_ignoredOutput = array();
    
    /**
     * Attributes to be mapped on input and then
     * mapped back before output. This allows us to
     * have more readable fields in the api than in 
     * the database.
     *
     * @var array
     */
    protected $_mapAttributes = array();
    
    /**
     * Required attributes for input
     *
     * @var array
     */
    protected $_required = array();
    
    /**
     * Identifies boolean input attributes
     *
     * @var array
     */
    protected $_booleans = array();
    
    /**
     * Identifies filters that should be treated
     * as dates and what sql operators to use.
     *
     * @var array
     */
    protected $_dateFilters = array();
    
    /**
     * Fields which are stored as csv arrays.
     * Eg. storefront_ids = 1,2,3
     * 
     * The values given through the api will be in 
     * the form array(1,2,3) or an integer 1
     *
     * @var unknown_type
     */
    protected $_arrayFilters = array();
    
    /**
     * Fields which are in tables other than the 
     * main table of the resource.
     * Eg. when joining two tables and filtering on the second
     *  array ('storefront_id' => 'rewardsinstore_quote')
     *
     * @var unknown_type
     */
    protected $_tableFilters = array();
    
    
    /**
     * Re-map API parameters to correct database fields,
     * and filter allowed params.
     *
     * @param stdClass $data
     * @return array
     */
    protected function _prepareInput($data)
    {
        /**
         * $data is in the form array('field' => 'value', 'field2' => 'value2'))
         * $this->_ignoredParams is in the form array(0 => 'allowedField', 1 => 'allowedField2)
         *
         * So we flip the _ignoredParams so allowedFields are array keys, and do an array_intersect_key
         */   
        $data = array_diff_key($data, array_flip($this->_ignoredParams));
        
        // map all aliased parameters over to their respective database-centric field names
        foreach ($this->_mapAttributes as $attributeAlias=>$attributeCode) {
            if(array_key_exists($attributeAlias, $data)) {
                $data[$attributeCode] = $data[$attributeAlias];
                unset($data[$attributeAlias]);
            }
        }
        return $data;
    }
    
    protected function _checkRequirements($method, $data)
    {
        if (isset($this->_required[$method])) {
            foreach ($this->_required[$method] as $requiredField) {
                if (!isset($data[$requiredField])) {
                    throw new Exception($requiredField);
                }
            }
        }
    }
    
    protected function _cleanBooleanInput($data)
    {
        foreach ($this->_booleans as $key) {
            if(array_key_exists($key, $data)) {
                // if value is "false" return false, otherwise return default interpretation
                if ($data[$key] === 'false') {
                    $data[$key] = 0;
                } else {
                    $data[$key] = $data[$key] ? 1 : 0;
                }
            }
        }
        return $data;
    }
    
    protected function _prepareEntity($entity, $attributes = array())
    {
        $data = $entity->toArray();
        
        foreach ($this->_mapAttributes as $attributeAlias => $attributeCode) {
            $data[$attributeAlias] = (isset($data[$attributeCode]) ? $data[$attributeCode] : null);
            unset($data[$attributeCode]);
        }
        
        if (!empty($attributes)) {
            foreach ($data as $field => $value) {
                if (!in_array($field, $attributes)) {
                    unset($data[$field]);
                }
            }
        }
        
        // Filter output
        foreach ($this->_ignoredOutput as $field) {
            // Note: isset is false on null values, so we use array_key_exists to detect the field
            if (array_key_exists($field, $data)) {
                unset($data[$field]);
            }
        }
        
        return $data;
    }
    
    protected function _prepareCollection($collection, $attributes = array())
    {
        $list = array();
        
        foreach ($collection as $entity) {
            $list[] = $this->_prepareEntity($entity, $attributes);
        }
        
        return $list;
    }
    
    /**
     * This function converts any date filters
     * into the appropriate values for a magento
     * filter on a collection.
     * 
     * $_dateFilters should be set in the child class.
     *
     * @param unknown_type $filters
     */
    protected function _applyFilters($collection, $filters)
    {
        foreach ($filters as $field => $value) {
            
            if (array_key_exists($field, $this->_dateFilters)) {
                
                $comparison = $this->_dateFilters[$field];
                
                /**
                 * This is how Magento does date comparisons
                 * 
                 * Eg. array (
                 *      'from' => '23 August 2011'
                 *      'date' => true
                 * )
                 */
                $filters[$field] = array ( 
                    array (
                        $comparison => $value,
                        'date' => true),
                    array (
                        'null' => true) // OR if date is null (we want to always show rules with no start or end date.)
                );
            } else if (in_array($field, $this->_arrayFilters)) {
                
                // Maps to the sql command for find_in_set which searches the csv for $value. Awesome.
                $filters[$field] = array (
                    'finset' => $value
                ); 
                
            }
        }
        
        foreach ($filters as $key => $value) {
            // Map filters if necessary
            if (array_key_exists($key, $this->_mapFilters)) {
                $key = $this->_mapFilters[$key];
            }
            
            $tablePrifix = 'main_table';
            if (array_key_exists($key, $this->_tableFilters)) {
                $tablePrifix = $this->_tableFilters[$key];
            }
            
            $collection->addFieldToFilter($tablePrifix.'.'.$key, $value);
        }
        
        return $collection;
    }
    
    protected function __()
    {
        return Mage::helper('rewardsinstore')->__(func_get_args());
    }
}
