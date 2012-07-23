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
 
class GoMage_Navigation_Model_Layer_Filter_Decimal extends GoMage_Navigation_Model_Layer_Filter_Abstract
{	
	
	const MIN_RANGE_POWER = 10;

    /**
     * Resource instance
     *
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal
     */
    protected $_resource;

    /**
     * Initialize filter and define request variable
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'decimal';
    }

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_decimal');
        }
        return $this->_resource;
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Catalog_Block_Layer_Filter_Decimal $filterBlock
     * @return Mage_Catalog_Model_Layer_Filter_Decimal
     */
    
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
		switch($this->getAttributeModel()->getFilterType()):
    	
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT):
    	case (GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER):    
    		
    		$_from = $request->getParam($this->getRequestVar().'_from', false);
    		$_to = $request->getParam($this->getRequestVar().'_to', false);
    		
    		if($_from || $_to){
    			
    			$value = array('from'=>$_from, 'to'=>$_to);
    			
    			$this->_getResource()->applyFilterToCollection($this, $value);
    			
    			$store      = Mage::app()->getStore();
        		$fromPrice  = $store->formatPrice($_from);
        		$toPrice    = $store->formatPrice($_to);
        		
    			
    			$this->getLayer()->getState()->addFilter(
	                $this->_createItem(Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice), $value)
	            );
    		
    		}else{
    			return $this;
    		}
    		
    	break;
    		
    	default:
    	
        /**
         * Filter must be string: $index,$range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) < 2) {
            return $this;
        }
		
		$length = count($filter);
		
		$value = array();
		
		for($i = 0; $i<$length; $i+=2){
			
			$value[] = array(
				'index'=>$filter[$i],
				'range'=>$filter[$i+1],
			);
			
		}
        if (!empty($value)) {
        	
            $this->setRange((int)$value[0]['range']);
			
            $this->_getResource()->applyFilterToCollection($this, $value);
            
            foreach($value as $_value){
            
	            $this->getLayer()->getState()->addFilter(
	                $this->_createItem($this->_renderItemLabel($_value['range'], $_value['index']), $_value)
	            );
            
            }
            
        }
        break;
        
        endswitch;
        
        return $this;
		
    }
    /**
     * Retrieve price aggreagation data cache key
     *
     * @return string
     */
    protected function _getCacheKey()
    {
        $key = $this->getLayer()->getStateKey()
            . '_ATTR_' . $this->getAttributeModel()->getAttributeCode();
        return $key;
    }
     protected function _getSelectedOptions(){
    	
    	if(is_null($this->_selected_options)){
    		
    		$selected = array();
        
	        if($value = Mage::app()->getFrontController()->getRequest()->getParam($this->getRequestVar())){
	        
	        	$_selected = array_merge($selected, explode(',', $value));
	        	
	        	$length = count($_selected);
			
				for($i = 0; $i<$length; $i+=2){
					
					$selected[] = $_selected[$i].','.$_selected[$i+1];
					
				}
	        	
	        
	        }
	        
	        $this->_selected_options = $selected;
    		
    	}
    	
    	return $this->_selected_options;
    	
    }
    
    /**
     * Retrieve data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
    	
		$key = $this->_getCacheKey();
		
		$selected = $this->_getSelectedOptions();
		
		
        $data = $this->getLayer()->getAggregator()->getCacheData($key);
        
        $filter_mode = Mage::helper('gomage_navigation')->isGomageNavigation();
        
        if ($data === null) {
            $range      = $this->getRange();
            $dbRanges   = $this->getRangeItemCounts($range);
            $data       = array();
			
			if($selected){
				foreach($selected as $value){
					$value = explode(',', $value);
					
					$dbRanges[$value[0]] = 0;
				}
				
				ksort($dbRanges);
			}
			
			
            foreach ($dbRanges as $index=>$count) {
            	
            	$value = $index . ',' . $range;
            	
            	if(in_array($value, $selected) && !$filter_mode){
            		continue;
            	}
            	
            	if(in_array($value, $selected) && $filter_mode){
            		
            		$active = true;
            		
            	}else{
					
					$active = false;
					
					if(!empty($selected) && $this->getAttributeModel()->getFilterType() != GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN ){
		                
		        		$value = implode(',',array_merge($selected, (array)$value));
		        		
		        	
		        	}
	        	
	        	}
            	
                $data[] = array(
                    'label' 	=> $this->_renderItemLabel($range, $index),
                    'value' 	=> $value,
                    'count' 	=> $count,
                    'active'	=> $active,
                );
                
            }

            $tags = array(
                Mage_Catalog_Model_Product_Type_Price::CACHE_TAG,
            );
            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        
        return $data;
    }
    
    public function getResetValue($value_to_remove = null)
    {
    	
    	
    	if($value_to_remove && ($current_value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar))){
    		
    		if(is_array($value_to_remove)){
    			
    			if(isset($value_to_remove['index']) && isset($value_to_remove['range'])){
    		
	    		$value_to_remove = $value_to_remove['index'].','.$value_to_remove['range'];
	    		
	    		}else{
	    			
	    			return null;
	    			
	    		}
	    		
	    	}
	    	
    		$current_value = $this->_getSelectedOptions();
    		
    		if(false !== ($position = array_search($value_to_remove, $current_value))){
    			
    			unset($current_value[$position]);
    			
    			if(!empty($current_value)){
    				
    				return implode(',', $current_value);
    				
    			}
    			
    		}
    		
    		
    		
    	}
    	
        return null;
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
        $from   = Mage::app()->getStore()->formatPrice(($value - 1) * $range, false);
        $to     = Mage::app()->getStore()->formatPrice($value * $range, false);
        return Mage::helper('catalog')->__('%s - %s', $from, $to);
    }

    /**
     * Retrieve maximum value from layer products set
     *
     * @return float
     */
    public function getMaxValue()
    {
        $max = $this->getData('max_value');
        if (is_null($max)) {
            list($min, $max) = $this->_getResource()->getMinMax($this);
            $this->setData('max_value', $max);
            $this->setData('min_value', $min);
        }
        return $max;
    }

    /**
     * Retrieve minimal value from layer products set
     *
     * @return float
     */
    public function getMinValue()
    {
        $min = $this->getData('min_value');
        if (is_null($min)) {
            list($min, $max) = $this->_getResource()->getMinMax($this);
            $this->setData('max_value', $max);
            $this->setData('min_value', $min);
        }
        return $min;
    }
    
    public function getMinValueInt()
    {
        return floor($this->getMinValue());
    }
    
    public function getMaxValueInt()
    {
        return ceil($this->getMaxValue());
    }

    /**
     * Retrieve range for building filter steps
     *
     * @return int
     */
    public function getRange()
    {
        $range = $this->getData('range');
        if (is_null($range)) {
            $max = $this->getMaxValue();
            $index = 1;
            do {
                $range = pow(10, (strlen(floor($max)) - $index));
                $items = $this->getRangeItemCounts($range);
                $index ++;
            }
            while($range > self::MIN_RANGE_POWER && count($items) < 2);

            $this->setData('range', $range);
        }
        return $range;
    }

    /**
     * Retrieve information about products count in range
     *
     * @param int $range
     * @return int
     */
    public function getRangeItemCounts($range)
    {
        $rangeKey = 'range_item_counts_' . $range;
        $items = $this->getData($rangeKey);
        if (is_null($items)) {
            $items = $this->_getResource()->getCount($this, $range);
            $this->setData($rangeKey, $items);
        }
        return $items;
    }

}
