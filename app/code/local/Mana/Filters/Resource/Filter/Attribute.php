<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Resource type which contains sql code for applying filters and related operations
 * @author Mana Team
 * Injected instead of standard resource catalog/layer_filter_attribute in 
 * Mana_Filters_Model_Filter_Attribute::_getResource().
 */
class Mana_Filters_Resource_Filter_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute {
    /** 
     * Modifies product collection select sql to include only those products which conforms this filter's conditions
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param string $value
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute::applyFilterToCollection()
     */
    public function applyFilterToCollection($filter, $value)
    {
		$collection = $filter->getLayer()->getProductCollection();
    	
		// MANA BEGIN: prevent product to appear twice if it conforms joined codition 2 times (e.g. if product
    	// has two values assigned for an attribute and both are filtered).
		$collection->getSelect()->distinct(true);
    	// MANA END
    	
		$attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            // MANA BEGIN: apply multiple values for filtered field
	        "{$tableAlias}.value in (".implode(',', array_filter(explode('_', $value))).")"
//            $connection->quoteInto("{$tableAlias}.value = ?", $value)
            // MANA END
        );

        $collection->getSelect()
            ->distinct()
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array()
            );

        return $this;
    }
    
    /**
     * For each option visible to person as a filter choice counts how many products are there given that all the 
     * other filters are applied
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @return array Each entry in result is int option_id => int count
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute::getCount()
     */
    public function getCount($filter)
    {
    	// clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

		// MANA BEGIN: if there is already applied filter with the same name, then unjoin it from select.
		// TODO: comment on Mage::registry('mana_cat_index_from_condition') after we edit category filters
        $from = array();
		$catIndexCondition = Mage::registry('mana_cat_index_from_condition');
	    foreach ($select->getPart(Zend_Db_Select::FROM) as $key => $value) {
			if ($key != $tableAlias) {
				if ($catIndexCondition && ($catIndexCondition == $value['joinCondition'])) {
	        		$value['joinCondition'] = Mage::registry('mana_cat_index_to_condition');
				}
        		$from[$key] = $value;
			}
		}
		$select->setPart(Zend_Db_Select::FROM, $from);
		// MANA END
        
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => "COUNT(DISTINCT {$tableAlias}.entity_id)"))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }
    
}