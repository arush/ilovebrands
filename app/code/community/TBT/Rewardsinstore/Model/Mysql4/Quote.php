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
class TBT_Rewardsinstore_Model_Mysql4_Quote extends Mage_Sales_Model_Mysql4_Quote
{
    // override parent members to ensure our mashing logic works properly
    protected $_useIsObjectNew = true;
    protected $_isPkAutoIncrement = false;
    
    protected function _construct()
    {
        $this->_init('rewardsinstore/quote', 'quote_id');
    }
    
    /**
     * Retrieve select object for load object data
     *
     * @param   string $field
     * @param   mixed $value
     * @return  Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        
        // join the base order's data to allow us to treat this as a full-fledged order
        $base = Mage::getModel('sales/quote')->getResource();
        $select->joinLeft($base->getMainTable(), "{$base->getMainTable()}.{$base->getIdFieldName()} = {$this->getMainTable()}.{$this->getIdFieldName()}");
        
        // if we're polluting this model, then we must allow the user to load a regular order
        // into it, with Instore fields null, so we need a fancy-pants UNION in the query!
        if ($object->getQuotePollution()) {
            $select2 = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where("{$base->getMainTable()}.{$base->getIdFieldName()}=?", $value)
                ->joinRight($base->getMainTable(), "{$base->getMainTable()}.{$base->getIdFieldName()} = {$this->getMainTable()}.{$this->getIdFieldName()}");
            $select = $this->_getReadAdapter()->select()->union(array($select, $select2));
        }
        
        return $select;
    }
    
    /**
     * Retrieve grid table
     *
     * @return string
     */
    public function getGridTable()
    {
        return parent::getGridTable() ?
            Mage::getModel('sales/quote')->getResource()->getMainTable() . '_grid'
                : false;
    }
}
