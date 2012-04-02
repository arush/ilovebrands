<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied price type filter
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Price constructor.
 */
class Mana_Filters_Model_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal {
    /**
     * Apply price filter to product collection
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mana_Filters_Block_Filter_Price $filterBlock
     * @return  Mana_Filters_Model_Filter_Price
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see app/code/core/Mage/Catalog/Model/Layer/Filter/Mage_Catalog_Model_Layer_Filter_Price::apply()
     */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        // MANA BEGIN: read multiple price ranges from URL instead of single range read in standard code, 
        // apply multiple ranges, show multiple ranges in state and do not hide selected ranges from filter options
        $selections = $this->getMSelectedValues();
        
        if (count($selections) > 0) {
        	list($index, $range) = explode(',', $selections[0]);
        	if ((int)$range) {
	            $this->setRange((int)$range);
	            $this->_applyToCollectionEx($selections);
		        foreach ($selections as $selection) {
        			list($index, $range) = explode(',', $selection);
		        	$this->getLayer()->getState()->addFilter($this->_createItemEx(array(
	            		'label' => $this->_renderItemLabel($range, $index), 
	            		'value' => $selection,
	            		'm_selected' => true,
	            	)));
		        }
        	}
//            $this->_items = array();
        }
        // MANA END
        return $this;
    }
    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $range = $this->_getResource()->getRange($value, $range);
        $result = new Varien_Object();
        Mage::dispatchEvent('m_render_price_range', array('range' => $range, 'model' => $this, 'result' => $result));
        if ($result->getLabel()) {
            return $result->getLabel();
        }
        else {
            $store      = Mage::app()->getStore();
            $fromPrice  = $store->formatPrice($range['from']);
            $toPrice    = $store->formatPrice($range['to']);
            return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
        }
    }
    
    /**
     * Applies one or more price filters to currently viewed product collection
     * @param array $selections
     * @return Mana_Filters_Model_Filter_Price
     * This method is cloned from method _applyToCollection() in parent class (method body was pasted from parent class 
     * completely rewritten.
     * Standard method did not give us possibility to filter multiple ranges. 
     */
    protected function _applyToCollectionEx($selections)
    {
        $this->_getResource()->applyFilterToCollectionEx($this, $selections);
        return $this;
    }
    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     * This method is cloned from method _createItem() in parent class (method body was pasted from parent class 
     * completely rewritten.
     * Standard method did not give us possibility to initialize non-standard fields. 
     */
    protected function _createItemEx($data)
    {
        return Mage::getModel('mana_filters/item')
            ->setData($data)
            ->setFilter($this);
    }
    /** 
     * Initializes internal array of in-memory representations of options of a filter
     * @return Mana_Filters_Model_Filter_Attribute
     * @see Mage_Catalog_Model_Layer_Filter_Abstract::_initItems()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
        	// MANA BEGIN
            $items[] = $this->_createItemEx($itemData);
            // MANA END
        }
        // MANA BEGIN: enable additional filter item processing
    	/* @var $ext Mana_Filters_Helper_Extended */ $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $items = $ext->processFilterItems($this, $items);
        // MANA END
        $this->_items = $items;
        return $this;
    }
    /**
     * Returns all values currently selected for this filter
     */
    public function getMSelectedValues() {
    	$values = Mage::app()->getRequest()->getParam($this->_requestVar);
		return $values ? explode('_', $values) : array();
//		$result = array();
//		foreach ($values as $value) {
//        	list($index, $range) = explode(',', $value);
//        	$result[] = array('index' => $index, 'range' => $range);
//		}    
//		return $result;
    }
    /** 
     * Depending on current filter values, returns available filter options from database
     * and additionally whether individual options are selected or not.
     * @return array
     * @see Mage_Catalog_Model_Layer_Filter_Price::_getItemsData()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _getItemsData()
    {
        $range      = $this->getRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();

        // MANA BEGIN
        $selectedIndexes = array();
        foreach ($this->getMSelectedValues() as $selection) {
        	list($index, $range) = explode(',', $selection);
        	$selectedIndexes[] = $index;
        }
        // MANA END
        
        foreach ($dbRanges as $index=>$count) {
            $data[] = array(
                'label' => $this->_renderItemLabel($range, $index),
                'value' => $index . ',' . $range,
                'count' => $count,
		        // MANA BEGIN
        	    'm_selected' => in_array($index, $selectedIndexes),
		        // MANA END
            );
        }

        return $data;
    }
    /** 
     * This method locates resource type which should do all dirty job with the database. In this override, we 
     * instruct Magento to take our resource type, not standard. 
     * @see Mage_Catalog_Model_Layer_Filter_Price::_getResource()
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel((string)$this->getDisplayOptions()->resource);
        }
        return $this->_resource;
    }
	public function getLowestPossibleValue() {
		return (int)$this->getMinValue();
	}
	public function getHighestPossibleValue() {
        $result = (int)ceil($this->getMaxValue());
        $min = $this->getLowestPossibleValue();
        return $result != $min ? $result : $result + 1;
    }
	public function getCurrentRangeLowerBound() {
		$selections = $this->getMSelectedValues();
		if ($selections && count($selections) == 1) {
        	list($index, $range) = explode(',', $selections[0]);
        	return $index;
		}
		else {
			return $this->getLowestPossibleValue();
		}
	}
	public function getCurrentRangeHigherBound() {
		$selections = $this->getMSelectedValues();
		if ($selections && count($selections) == 1) {
        	list($index, $range) = explode(',', $selections[0]);
        	return $range;
		}
		else {
			return $this->getHighestPossibleValue();
		}
	}
    public function getRemoveUrl() {
    	$query = array($this->getRequestVar()=>$this->getResetValue());
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    public function getName() {
    	return $this->getFilterOptions()->getName();
    }
    public function getRange() {
        $range = $this->getData('range');
        if (!$range) {
            if (Mage::helper('mana_db')->hasOverriddenValueEx($this->getFilterOptions(), 24)) {
                $range = (float)$this->getFilterOptions()->getRangeStep();
            }
            elseif (Mage::helper('mana_db')->hasOverriddenValueEx($this->getFilterOptions(), 24, 'global_default_mask')) {
                $range = (float)$this->getFilterOptions()->getGlobalRangeStep();
            }
        }
        if (!$range) {
            $maxValue = $this->getMaxValue();
            $index = 1;
            do {
                $range = pow(10, (strlen(floor($maxValue)) - $index));
                $items = $this->getRangeItemCounts($range);
                $index++;
            }
            while ($range > self::MIN_RANGE_POWER && count($items) < 2);
            $this->setData('range', $range);
        }

        return $range;
    }
}