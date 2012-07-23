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
 * @since        Class available since Release 2.0
 */
 
class GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal extends Mage_Core_Model_Mysql4_Abstract
{
    private $_min_max = array();
    
    /**
     * Initialize connection and define main table name
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_index_eav_decimal', 'entity_id');
    }
	
	public function prepareSelect($filter, $value, $select){
		
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", Mage::app()->getStore()->getId())
        );

        $select->join(
            array($tableAlias => $this->getMainTable()),
            join(' AND ', $conditions),
            array()
        );
		
		$priceExpr = "{$tableAlias}.value";
		
		$where = array();
		
		switch($filter->getAttributeModel()->getFilterType()):
		
		case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER):	
    		

    		$from	= isset($value['from']) ? $value['from'] : 0;
    		$to		= isset($value['to']) ? $value['to'] : 0;

    		$where[] = sprintf("{$tableAlias}.value >= %s", $from) . ($to > 0 ? ' AND ' . sprintf("{$tableAlias}.value <= %d", $to) : '');
    		
    	break;
		
		default:
		
			foreach((array)$value as $_value){
				
				$where[] = sprintf("{$tableAlias}.value >= %s", ($_value['range'] * ($_value['index'] - 1))) . ' AND ' . sprintf("{$tableAlias}.value < %d", ($_value['range'] * $_value['index']));
				
			}
		
		break;
		
		endswitch;
		
        $select->where(implode(' OR ', $where));
        
        
		
	}
	
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param float $range
     * @param int $index
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal
     */
    public function applyFilterToCollection($filter, $value)
    {
    	
    	$collection = $filter->getLayer()->getProductCollection();
        $select     = $collection->getSelect();
        
        $this->prepareSelect($filter, $value, $select);
        
        $attribute_code = $filter->getRequestVar();;
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        foreach($base_select as $code=>$select){
        	
        	
        	if($attribute_code != $code){
        	
        		$this->prepareSelect($filter, $value, $select);
        	
        	}
        	
        }
        
        return $this;
    	
    }

    /**
     * Retrieve array of minimal and maximal values
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @return array
     */
    
    public function getMinMax($filter)
    {
        if(!count($this->_min_max))
        {
            $select     = $this->_getSelect($filter);
            $adapter    = $this->_getReadAdapter();
    
            $select->columns(array(
                'min_value' => new Zend_Db_Expr('MIN(decimal_index.value)'),
                'max_value' => new Zend_Db_Expr('MAX(decimal_index.value)'),
            ));
    
            $result     = $adapter->fetchRow($select);
           
            return array($result['min_value'], $result['max_value']);
            
        }else{
            
            return $this->_min_max;
            
        }
    }
    

    /**
     * Retrieve clean select with joined index table
     *
     * Joined table has index
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @return Varien_Db_Select
     */
    protected function _getSelect($filter)
    {
    	
    	$base_select = $filter->getLayer()->getBaseSelect();
    	
    	if(isset($base_select[$filter->getRequestVar()])){
        	
        	$select = $base_select[$filter->getRequestVar()];
        
        }else{
    	
        	$collection = $filter->getLayer()->getProductCollection();
        	$select = clone $collection->getSelect();
        }
        
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
		$select->reset(Zend_Db_Select::GROUP);
		$select->reset(Zend_Db_Select::WHERE);        
				
        $attributeId = $filter->getAttributeModel()->getId();
        $storeId     = Mage::app()->getStore()->getId();

        $select->join(
            array('decimal_index' => $this->getMainTable()),
            "e.entity_id=decimal_index.entity_id AND decimal_index.attribute_id={$attributeId}"
                . " AND decimal_index.store_id={$storeId}",
            array()
        );

        return $select;
    }

    /**
     * Retrieve array with products counts per range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select     = $this->_getSelect($filter);
        $adapter    = $this->_getReadAdapter();

        $countExpr  = new Zend_Db_Expr("COUNT(*)");
        $rangeExpr  = new Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group('range');

        return $adapter->fetchPairs($select);
    }
}
