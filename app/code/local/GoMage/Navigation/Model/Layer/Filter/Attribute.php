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

class GoMage_Navigation_Model_Layer_Filter_Attribute extends GoMage_Navigation_Model_Layer_Filter_Abstract
{
    const OPTIONS_ONLY_WITH_RESULTS = 1;

    /**
     * Resource instance
     *
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    protected $_resource;

    /**
     * Construct attribute filter
     *
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->_requestVar = 'attribute';
        
    }

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_attribute');
        }
        return $this->_resource;
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  unknown
     */
    protected function _getOptionText($optionId)
    {
        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
    	
        $filter = $request->getParam($this->_requestVar);
        
        if (is_array($filter)) {
            return $this;
        }
        
        if ($filter) {
        	
        	$filters = explode(',', $filter);
        	
            $this->_getResource()->applyFilterToCollection($this, $filters);
            
            foreach($filters as $filter){
            	
            	$text = $this->_getOptionText($filter);
            	
	            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
	            
            
            }
            
        }
        return $this;
    }

    /**
     * Check whether specified attribute can be used in LN
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterable();
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
     
     
    protected function _getItemsData()
    {
    	
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();
        
        $option_images = $attribute->getOptionImages();
        
        $selected = array();
        
        if($value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)){
        
        	$selected = array_merge($selected, explode(',', $value));
        
        }
        
        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);
        
        $filter_mode = Mage::helper('gomage_navigation')->isGomageNavigation();
        

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();

            foreach ($options as $option) {
            	
            	$image = '';
            	
                if (is_array($option['value']) || (in_array($option['value'], $selected) && !$filter_mode)) {
                    continue;
                }
                
                
                
                if (Mage::helper('core/string')->strlen($option['value'])) {
                	
                	if($option_images && isset($option_images[$option['value']])){
                		
                		$image = $option_images[$option['value']];
                		
                	}
                	
                	if(in_array($option['value'], $selected) && $filter_mode){
                		
                		$active = true;
                		
                		$value = $option['value'];
                		
                	}else{
                		
                		$active = false;
                		
	                	if(!empty($selected) && $attribute->getFilterType() != GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN){
	                	
	                		$value = implode(',',array_merge($selected, (array)$option['value']));
	                	
	                	}else{
	                		
	                		$value = $option['value'];
	                		
	                	}
	                	
                	}
                	
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']]) || in_array($option['value'], $selected)) {
                            $data[] = array(
                                'label' 	=> $option['label'],
                                'value' 	=> $value,
                                'count' 	=> isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                                'active'	=> $active,
                                'image'		=> $image,
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $value,
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                            'active'	=> $active,
                            'image'		=> $image,
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
    
    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue($value_to_remove = null)
    {
    	
    	if($value_to_remove && ($current_value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar))){
    		
    		$current_value = explode(',', $current_value);
    		
    		if(false !== ($position = array_search($value_to_remove, $current_value))){
    			
    			unset($current_value[$position]);
    			
    			if(!empty($current_value)){
    				
    				return implode(',', $current_value);
    				
    			}
    			
    		}
    		
    		
    	}
    	
        return null;
    }
}
