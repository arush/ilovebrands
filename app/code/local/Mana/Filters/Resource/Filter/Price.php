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
 * Mana_Filters_Model_Filter_Price::_getResource().
 */
class Mana_Filters_Resource_Filter_Price extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price {
    /**
     * Applies one or more price filters to currently viewed product collection
     * @param Mana_Filters_Model_Filter_Price $filter
     * @param array $selections
     * @return Mana_Filters_Resource_Filter_Price
     * This method is cloned from method applyFilterToCollection() in parent class (method body was pasted from parent class 
     * and changed as needed. All changes marked with comments
     * Standard method did not give us possibility to filter multiple ranges. 
     */
    public function applyFilterToCollectionEx($filter, $selections)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        $select     = $collection->getSelect();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table      = $this->_getIndexTableAlias();
        $additional = join('', $response->getAdditionalCalculations());
        $fix = $this->_getConfigurablePriceFix();
        $rate       = $filter->getCurrencyRate();
        $precision = $filter->getDecimalDigits();
        $priceExpr  = new Zend_Db_Expr("ROUND(({$table}.min_price {$additional} {$fix}) * {$rate}, $precision)");

		// MANA BEGIN: modify select formation to include multiple price ranges
        $condition = '';
        foreach ($selections as $selection) {
        	list($index, $range) = explode(',', $selection);
        	$range = $this->getPriceRange($index, $range);
        	if ($condition != '') $condition .= ' OR ';
        	$condition .= '(('.$priceExpr . ' >= '. $range['from'].') '.
        		'AND ('.$priceExpr . ($this->_isUpperBoundInclusive() ? ' <= ' : ' < '). $range['to'].'))';
        }
        $select
            ->distinct()
        	->where($condition);
        // MANA END
        return $this;
    }
    protected function _isUpperBoundInclusive() {
        return false;
    }
    /**
     * For each option visible to person as a filter choice counts how many products are there given that all the 
     * other filters are applied
     * @param Mana_Filters_Model_Filter_Price $filter
     * @param int $range The whole price range is split into several using this range step
     * @return array Each entry in result is int index => int count
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute::getCount()
     */
    public function getCount($filter, $range)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);
        $table      = $this->_getIndexTableAlias();

        $additional = join('', $response->getAdditionalCalculations());
        $fix = $this->_getConfigurablePriceFix();
        $rate       = $filter->getCurrencyRate();
        $countExpr  = new Zend_Db_Expr('COUNT(*)');
        $rangeExpr  = new Zend_Db_Expr("FLOOR((({$table}.min_price {$additional} {$fix}) * {$rate}) / {$range}) + 1");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        
        // MANA BEGIN: make sure price filter is not applied
        Mage::helper('mana_filters')->resetProductCollectionWhereClause($select);
        // MANA END
        
        $select->where("{$table}.min_price > 0");
        $select->group('range');

        $result = $connection->fetchPairs($select);
//        Mage::log('COUNT select: '.((string)$select), Zend_Log::DEBUG, 'price.log');
//        Mage::log("COUNT keys: ". implode(', ', array_keys($result)), Zend_Log::DEBUG, 'price.log');
//        Mage::log("COUNT values: ". implode(', ', array_values($result)), Zend_Log::DEBUG, 'price.log');
        return $result;
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

        $additional   = join('', $response->getAdditionalCalculations());
        $maxPriceExpr = new Zend_Db_Expr("MAX({$table}.min_price {$additional}) AS m_max_price");

        // MANA BEGIN: make sure no filter is applied
        Mage::helper('mana_filters')->resetProductCollectionWhereClause($select);
        $from = $select->getPart(Zend_Db_Select::FROM);
        foreach ($from as $key => $options) {
        	if ($key == 'cat_index') {
        		/* @var $layer Mage_Catalog_Model_Layer */ $layer = Mage::getSingleton('catalog/layer');
        		$needle = "cat_index.category_id='";
        		$startPos = strpos($options['joinCondition'], $needle);
        		if ($startPos === false)  throw new Exception('Not implemented');
        		$endPos = strpos($options['joinCondition'], "'", $startPos + strlen($needle));
        		$from[$key]['joinCondition'] = 
        			substr($options['joinCondition'], 0, $startPos + strlen($needle)).
        			$layer->getCurrentCategory()->getId().
        			substr($options['joinCondition'], $endPos);
        	}
        	elseif (strrpos($key, '_idx') === strlen($key) - strlen('_idx')) {
        		unset($from[$key]);
        	}
        }
        $select->setPart(Zend_Db_Select::FROM, $from);
        // MANA END
        $select->columns(array($maxPriceExpr))->order('m_max_price DESC');

        $result  = $connection->fetchOne($select) * $filter->getCurrencyRate();
//        Mage::log('MAX select: ' . ((string)$select), Zend_Log::DEBUG, 'price.log');
//        Mage::log("MAX result: $result", Zend_Log::DEBUG, 'price.log');
//        Mage::log('LIST select: '. (string)$filter->getLayer()->getProductCollection()->getSelect(), Zend_Log::DEBUG, 'price.log');
//        $this->getCount($filter, 1);
        return $result;
    }

    public function getPriceRange($index, $range) {
    	return array('from' => $range * ($index - 1), 'to' => $range * $index);
    }

    protected function _getConfigurablePriceFix() {
        if (!Mage::getStoreConfigFlag('mana_filters/general/adjust_configurable_price')) {
            return '';
        }
        /* @var $db Mage_Core_Model_Resource */ $db = Mage::getSingleton('core/resource');
        $request = Mage::app()->getRequest();
        $subselect = '';

        $values = array();
        foreach (Mage::helper('mana_filters')->getFilterOptionsCollection() as $filter) {
            if ($filter->getType() == 'attribute' && ($param = $request->getParam($filter->getCode()))) {
                $values = array_merge($values, explode('_', $param));
            }
        }
        if (count($values) > 0) {
            $values = implode(',', $values);
            $subselect = "SELECT SUM(super_price.pricing_value) ".
                "FROM {$db->getTableName('catalog/product_super_attribute')} AS super ".
                "INNER JOIN {$db->getTableName('catalog/product_super_attribute_pricing')} AS super_price ".
                    "ON super.product_super_attribute_id = super_price.product_super_attribute_id AND ".
                        "super_price.is_percent = 0 AND super_price.value_index IN ($values) ".
                "WHERE super.product_id = e.entity_id";
        }
        return $subselect ? " + COALESCE(($subselect), 0)" : '';
    }
}