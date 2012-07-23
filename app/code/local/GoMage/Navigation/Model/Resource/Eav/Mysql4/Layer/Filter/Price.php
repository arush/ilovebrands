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

class GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Price extends Mage_Core_Model_Mysql4_Abstract
{
    
    /**
     * Initialize connection and define main table name
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_index_price', 'entity_id');
    }

    /**
     * Retrieve joined price index table alias
     *
     * @return string
     */
    protected function _getIndexTableAlias()
    {
        return 'price_index';
    }

    /**
     * Prepare response object and dispatch prepare price event
     *
     * Return response object
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param Varien_Db_Select $select
     * @return Varien_Object
     */
    protected function _dispatchPreparePriceEvent($filter, $select)
    {
        // prepare response object for event
        $response = new Varien_Object();
        $response->setAdditionalCalculations(array());

        // prepare event arguments
        $eventArgs = array(
            'select'          => $select,
            'table'           => $this->_getIndexTableAlias(),
            'store_id'        => $filter->getStoreId(),
            'response_object' => $response
        );

        /**
         * @deprecated since 1.3.2.2
         */
        Mage::dispatchEvent('catalogindex_prepare_price_select', $eventArgs);

        /**
         * @since 1.4
         */
        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);

        return $response;
    }
	
	/**
     * Retrieve minimal price for attribute
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMinPrice($filter)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table = $this->_getIndexTableAlias();

        $additional     = join('', $response->getAdditionalCalculations());
        $minPriceExpr   = new Zend_Db_Expr("MIN({$table}.min_price {$additional})");

        $select->columns(array($minPriceExpr));

        return $connection->fetchOne($select) * $filter->getCurrencyRate();
    }
	
    /**
     * Retrieve maximal price for attribute
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMaxPrice($filter)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table = $this->_getIndexTableAlias();

        $additional     = join('', $response->getAdditionalCalculations());
        $maxPriceExpr   = new Zend_Db_Expr("MAX({$table}.min_price {$additional})");

        $select->columns(array($maxPriceExpr));

        return $connection->fetchOne($select) * $filter->getCurrencyRate();
    }
    
    protected function _getSelect($filter)
    {
    	$base_select = $filter->getLayer()->getBaseSelect();
    	
    	if(isset($base_select['price'])){
        	
        	$select = $base_select['price'];
        
        }else{
        	
        	$collection = $filter->getLayer()->getProductCollection();
        	$collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());
        	$select = clone $collection->getSelect();
        	
        }
    	
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
		$select->reset(Zend_Db_Select::GROUP);
		
		
        return $select;
    }
	
    /**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
    	
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table = $this->_getIndexTableAlias();

        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $countExpr  = new Zend_Db_Expr("COUNT(DISTINCT e.entity_id)");
        $rangeExpr  = new Zend_Db_Expr("FLOOR((({$table}.min_price {$additional}) * {$rate}) / {$range}) + 1");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->where("{$table}.min_price > 0");
        $select->group('range');
        
        return $connection->fetchPairs($select);
    }
	
	public function prepareSelect($filter, $value, $select){
		
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table      = $this->_getIndexTableAlias();
        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $priceExpr  = new Zend_Db_Expr("(({$table}.min_price {$additional}) * {$rate})");
		
		$where = array();
		
		switch($filter->getAttributeModel()->getFilterType()):
		
		case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER):    
    		
    		
    		
    		$from	= isset($value['from']) ? intval($value['from']) : 0;
    		$to		= isset($value['to']) ? intval($value['to']) : 0;
    		
    		$where[] = sprintf($priceExpr . ' >= %s', $from) . ($to > 0 ? ' AND ' . sprintf($priceExpr . ' <= %d', $to) : '');
    		
    	break;
		
		default:
		
			foreach((array)$value as $_value){
				
				$where[] = sprintf($priceExpr . ' >= %s', ($_value['range'] * ($_value['index'] - 1))) . ' AND ' . sprintf($priceExpr . ' < %d', ($_value['range'] * $_value['index']));
				
			}
		
		break;
		
		endswitch;
		
        $select->where(implode(' OR ', $where));
        
	}
	
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @param int $index    the range factor
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        //$collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());
        $select     = $collection->getSelect();
        
        $this->prepareSelect($filter, $value, $select);
        
        $attribute_code = 'price';
        
        $base_select = $filter->getLayer()->getBaseSelect();
        
        foreach($base_select as $code=>$select){
        	
        	
        	if($attribute_code != $code){
        	
        		$this->prepareSelect($filter, $value, $select);
        	
        	}
        	
        }
        
        return $this;
    }
}
