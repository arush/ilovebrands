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

class GoMage_Navigation_Model_Layer_Filter_Price extends GoMage_Navigation_Model_Layer_Filter_Abstract
{
    const MIN_RANGE_POWER = 10;
	
	protected $_selected_options;
	
    /**
     * Resource instance
     *
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
     */
    protected $_resource;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'price';
    }

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_price');
        }
        return $this->_resource;
    }

    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {
        $range = $this->getData('price_range');
        if (is_null($range)) {
            $maxPrice = $this->getMaxPriceInt();
            $index = 1;
            do {
                $range = pow(10, (strlen(floor($maxPrice))-$index));
                $items = $this->getRangeItemCounts($range);
                $index++;
            }
            while($range>self::MIN_RANGE_POWER && count($items)<2);

            $this->setData('price_range', $range);
        }
        return $range;
    }
	
	/**
     * Get minimal price from layer products set
     *
     * @return float
     */
    public function getMinPriceInt()
    {
        $minPrice = $this->getData('min_price_int');
        if (is_null($minPrice)) {
            $minPrice = $this->_getResource()->getMinPrice($this);
            $minPrice = floor($minPrice);
            $this->setData('min_price_int', $minPrice);
        }
        return $minPrice;
    }
	
    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->getData('max_price_int');
        if (is_null($maxPrice)) {
            $maxPrice = $this->_getResource()->getMaxPrice($this);
            $maxPrice = ceil($maxPrice);
            $this->setData('max_price_int', $maxPrice);
        }
        return $maxPrice;
    }
    
    public function getMinValueInt()
    {
        return $this->getMinPriceInt();
    }
    
    public function getMaxValueInt()
    {
        return $this->getMaxPriceInt();
    }

    /**
     * Get information about products count in range
     *
     * @param   int $range
     * @return  int
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

    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $store      = Mage::app()->getStore();
        $fromPrice  = $store->formatPrice(($value-1)*$range);
        $toPrice    = $store->formatPrice($value*$range);
        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }

    /**
     * Get price aggreagation data cache key
     *
     * @return string
     */
    protected function _getCacheKey()
    {
        $key = $this->getLayer()->getStateKey()
            . '_PRICES_GRP_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
            . '_CURR_' . Mage::app()->getStore()->getCurrentCurrencyCode()
            . '_ATTR_' . $this->getAttributeModel()->getAttributeCode()
            . '_LOC_'
            ;
        $taxReq = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $key.= implode('_', $taxReq->getData());
        return $key;
    }
    
    protected function _getSelectedOptions(){
    	
    	if(is_null($this->_selected_options)){
    		
    		$selected = array();
        
	        if($value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)){
	        
	        	$value = urldecode($value);
	        	
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
     * Get data for build price filter items
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
            $range      = $this->getPriceRange();
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

    /**
     * Apply price range filter to collection
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
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
        	
            $this->setPriceRange((int)$value[0]['range']);
			
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
     * Retrieve active customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = $this->_getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $customerGroupId;
    }

    /**
     * Set active customer group id for filter
     *
     * @param int $customerGroupId
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    /**
     * Retrieve active currency rate for filter
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if (is_null($rate)) {
            $rate = Mage::app()->getStore($this->getStoreId())->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }

    /**
     * Set active currency rate for filter
     *
     * @param float $rate
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function setCurrencyRate($rate)
    {
        return $this->setData('currency_rate', $rate);
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
}
