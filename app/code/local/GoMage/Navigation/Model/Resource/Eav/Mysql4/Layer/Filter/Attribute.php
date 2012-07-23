<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 1.0
 */

class GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{

    
     
	public function prepareSelect($filter, $value, $select){
		
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        
        $alias = $attribute->getAttributeCode() . '_idx';
        
        $value = (array)$value;
        
        foreach($value as $_value){
			$where[] = intval($_value);
		}
        
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", Mage::app()->getStore()->getId()),
            $connection->quoteInto($tableAlias.'.value IN ('.implode(',', $where).')', $value)
        );
        
		
        $select->join(
            array($tableAlias => $this->getMainTable()),
            join(' AND ', $conditions),
            array()
        );
		
	}
     
     /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
     
     
    public function applyFilterToCollection($filter, $value)
    {
    	
    	$attribute_code = $filter->getAttributeModel()->getAttributeCode();
    	
        $collection = $filter->getLayer()->getProductCollection();
        
        $this->prepareSelect($filter, $value, $collection->getSelect());
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        foreach($base_select as $code=>$select){
        	
        	
        	if($attribute_code != $code){
        	
        		$this->prepareSelect($filter, $value, $select);
        	
        	}
        	
        }
        
        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
    	
    	$connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
    	
        // clone select from collection with filters
        //$select = clone $filter->getLayer()->getProductCollection()->getSelect();
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        if(isset($base_select[$attribute->getAttributeCode()])){
        
        	$select = $base_select[$attribute->getAttributeCode()];
        
        }else{
        	
        	$select = clone $filter->getLayer()->getProductCollection()->getSelect();
        	
        }
        
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);

        
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
