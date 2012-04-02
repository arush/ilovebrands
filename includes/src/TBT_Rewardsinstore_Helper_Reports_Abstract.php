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
abstract class TBT_Rewardsinstore_Helper_Reports_Abstract extends Mage_Core_Helper_Data
{
    /**
     * Helper collection
     *
     * @var Mage_Core_Model_Mysql_Collection_Abstract|Mage_Eav_Model_Entity_Collection_Abstract|array
     */
    protected  $_collection;
    
    /**
     * Parameters for helper
     *
     * @var array
     */
    protected  $_params = array();
    
    public function getCollection()
    {
        if(is_null($this->_collection)) {
            $this->_initCollection();
        }
        return $this->_collection;
    }
    
    abstract protected function _initCollection();
    
    /**
     * Returns collection items
     *
     * @return array
     */
    public function getItems()
    {
        return is_array($this->getCollection()) ? $this->getCollection() : $this->getCollection()->getItems();
    }
    
    public function getCount()
    {
        return sizeof($this->getItems());
    }
    
    public function getColumn($index)
    {
        $result = array();
        foreach ($this->getItems() as $item) {
            if (is_array($item)) {
                if(isset($item[$index])) {
                    $result[] = $item[$index];
                } else {
                    $result[] = null;
                }
            } elseif ($item instanceof Varien_Object) {
                $result[] = $item->getData($index);
            } else {
                $result[] = null;
            }
        }
        return $result;
    }
    
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }
    
    public function setParams(array $params)
    {
        $this->_params = $params;
    }
    
    public function getParam($name)
    {
        if(isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        
        return null;
    }
    
    public function getParams()
    {
        return $this->_params;
    }
}
