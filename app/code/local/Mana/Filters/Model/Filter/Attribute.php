<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied filter which is based on an attribute
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Attribute constructor.
 */
class Mana_Filters_Model_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute {
    /**
     * Apply attribute option filter to product collection
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mana_Filters_Block_Filter_Attribute $filterBlock
     * @return  Mana_Filters_Model_Filter_Attribute
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see app/code/core/Mage/Catalog/Model/Layer/Filter/Mage_Catalog_Model_Layer_Filter_Attribute::apply()
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        
        // MANA BEGIN: when several filter options can be applied, several labels should be added to layer 
        // state, on label for each selected option. Here we assume all option ids to be in URL as one string value
        // separated by '_'
//        $text = $this->_getOptionText($filter);
        $text = array();
        foreach ($this->getMSelectedValues() as $optionId) {
        	$text[$optionId] = $this->getAttributeModel()->getFrontend()->getOption($optionId);
        }
        // MANA END
        
        if ($filter && $text) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            // MANA BEGIN: create multiple items in layer state, prevent filter from hiding when value is set
	        foreach ($this->getMSelectedValues() as $optionId) {
            	$this->getLayer()->getState()->addFilter($this->_createItemEx(array(
            		'label' => $text[$optionId], 
            		'value' => $optionId,
            		'm_selected' => true,
            	)));
	        }
//            $this->_items = array();
            // MANA END
        }
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
		return $values ? array_filter(explode('_', $values)) : array();    
    }
    
    /** 
     * Depending on current filter values and on attribute settings, returns available filter options from database
     * and additionally whether individual options are selected or not.
     * @return array
     * @see Mage_Catalog_Model_Layer_Filter_Attribute::_getItemsData()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _getItemsData()
    {
    	// MANA BEGIN: from url, retrieve ids of all options currently selected
    	$selectedOptionIds = $this->getMSelectedValues();
    	// MANA END

    	$attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();

            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']]) || in_array($option['value'], $selectedOptionIds)) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                            	// MANA BEGIN: mark each selected item now in memory so we could later mark it 
                            	// visually in markup
                            	'm_selected' => in_array($option['value'], $selectedOptionIds),
                            	// MANA END
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        	// MANA BEGIN: mark each selected item now in memory so we could later mark it 
                            // visually in markup
                            'm_selected' => in_array($option['value'], $selectedOptionIds),
                            // MANA END
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            if ($sortMethod = $this->getFilterOptions()->getSortMethod()) {
                usort($data, array(Mage::getSingleton('mana_filters/sort'), $sortMethod));
            }
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
    
    /** 
     * This method locates resource type which should do all dirty job with the database. In this override, we 
     * instruct Magento to take our resource type, not standard. 
     * @see Mage_Catalog_Model_Layer_Filter_Attribute::_getResource()
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel((string)$this->getDisplayOptions()->resource);
        }
        return $this->_resource;
    }

    protected function _getIsFilterable()
    {
    	switch ($this->getMode()) {
    		case 'category': return $this->getFilterOptions()->getIsEnabled();
    		case 'search': return $this->getFilterOptions()->getIsEnabledInSearch();
    		default: throw new Exception('Not implemented');
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
    protected function _getIsFilterableAttribute($attribute)
    {
        return $this->_getIsFilterable(); //return $this->getFilterOptions()->getIsEnabled();
    }
    public function getName() {
    	return $this->getFilterOptions()->getName();
    }
}
