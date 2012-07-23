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

class GoMage_Navigation_Model_Layer_Filter_Category extends GoMage_Navigation_Model_Layer_Filter_Abstract
{
    /**
     * Active Category Id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Applied Category
     *
     * @var Mage_Catalog_Model_Category
     */
    protected $_appliedCategory = null;
    
    protected $category_list = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'cat';
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

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        //$this->_categoryId = $filter;
        
        $filters = explode(',', $filter);
        
        $value = array();
        
        foreach($filters as $filter){
        
	        $category = Mage::getModel('catalog/category')
	            ->setStoreId(Mage::app()->getStore()->getId())
	            ->load($filter);
        
        	if ($this->_isValidCategory($category)) {
        		$value[] = $filter;
        		
        		
        		$this->getLayer()->getState()->addFilter(
                	$this->_createItem($category->getName(), $filter)
            	);
        	}
        }
        
        if(!empty($value)){
        	
        	$this->_getResource()->applyFilterToCollection($this, $value);
        	
        }
        

        return $this;
    }

    /**
     * Validate category for be using as filter
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return unknown
     */
    protected function _isValidCategory($category)
    {
        return $category->getId();
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('catalog')->__('Category');
    }

    /**
     * Get selected category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        if (!is_null($this->_categoryId)) {
            $category = Mage::getModel('catalog/category')
                ->load($this->_categoryId);
            if ($category->getId()) {
                return $category;
            }
        }
        return $this->getLayer()->getCurrentCategory();
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    
    protected function _addChildsCategory($cat_id, $request)
    {
       $cats = array();
       $cats[] = $cat_id; 
       if (in_array($cat_id, $request))
       {
          $_cat = Mage::getModel('catalog/category')->load($cat_id);
          $cats_ids = explode(',', $_cat->getChildren());
          $str_children = '0';
          
          foreach ($cats_ids as $_id)
          {
              $str_children .= ',' . $this->_addChildsCategory($_id, $request);
          }
          
          if ($str_children != '0')
          {
             $cats = array_merge($cats, explode(',', $str_children));
          }
          
       }                     
       return implode(',', $cats);        
    }
    
    
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $category   = $this->getLayer()->getCurrentCategory();
            /** @var $categoty Mage_Catalog_Model_Categeory */
            //$categories = $categoty->getChildrenCategories();
            
            if (Mage::getStoreConfigFlag('gomage_navigation/category/show_allsubcats')){
            
                $cats_ids = array_diff($category->getAllChildren(true), array($category->getId()));
                
                if (count($cats_ids)) {
                    $cats_ids_str = implode(',', $cats_ids);
                } else {
                    $cats_ids_str = '0';   
                }
                
            }else {
                $cats_ids = array();                
                if ($category->getChildren())
                  $cats_ids = explode(',', $category->getChildren());
                                                                                                       
                $cats_ids_str = '0';
                
                foreach ($cats_ids as $_id)
                {                
                   $cats_ids_str .= ',' . $this->_addChildsCategory($_id,  explode(',',Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)));
                }
                                                
                foreach(explode(',',Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)) as $_id){                	 
                	$_cat = Mage::getModel('catalog/category')->load($_id);
                	$cats_ids_str .= ',' . $_cat->getId();
                	if ($_cat->getChildren()){
                		$cats_ids_str .= ',' . $_cat->getChildren();
                	}
                	$_parent_cat = Mage::getModel('catalog/category')->load($_cat->getParentId());                	                	
                	while($category->getLevel() < $_parent_cat->getLevel()){
                		$cats_ids_str .= ',' . $_parent_cat->getId(); 	
                		$_parent_cat = Mage::getModel('catalog/category')->load($_parent_cat->getParentId());
                	}                		
                }                                
            }
                                      
            $categories = Mage::getResourceModel('catalog/category_collection');
	        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
	        $categories->addAttributeToSelect('url_key')
	            ->addAttributeToSelect('name')
	            ->addAttributeToSelect('all_children')
	            ->addAttributeToSelect('level')
	            ->addAttributeToSelect('is_anchor')
	            ->addAttributeToFilter('is_active', 1)
	            ->addAttributeToSelect('filter_image')
	            ->addIdFilter($cats_ids_str)	            
	            ->joinUrlRewrite()	            	            
	            ->load();
	            	            
	        $category_list_ids = array();
	        foreach ($categories as $category){
	        	$category_list_ids[$category->getId()] = $category;
	        }    
	        
         	$category = $this->getLayer()->getCurrentCategory();	        
	        foreach($category->getChildrenCategories() as $_category){
	        	$this->_renderCategoryList($_category, $category_list_ids);
	        }
	            	            
	            	                    
			$selected = array();
	        
	        if($value = Mage::app()->getFrontController()->getRequest()->getParam($this->_requestVar)){
	        
	        	$selected = array_merge($selected, explode(',', $value));
	        
	        }
			
            $data = array();
            
            $filter_mode = Mage::helper('gomage_navigation')->isGomageNavigation();
            
        	if(count($this->category_list) > 0){
        		
        		$category_count = $this->_getResource()->getCount($this, $this->category_list);
        		        		        		        		
	            foreach ($this->category_list as $category) {
	                
	                if ($category->getIsActive()) {
	                	
	                	if(in_array($category->getId(), $selected) && !$filter_mode){	                		
	                		continue;	                		
	                	}
	                	
	                	if (Mage::getStoreConfig('gomage_navigation/category/hide_empty') && !isset($category_count[$category->getId()])){	                	    
	                	    continue;	                	    
	                	} 
	                	
	                	if(in_array($category->getId(), $selected) && $filter_mode){
	                		
	                		$active = true;
	                		
	                		$value = $category->getId();
	                		
	                	}else{
	                		
	                		$active = false;
		                	
		                	if(!empty($selected)){
		                		
		                		$value = $this->_prepareRequestValue($selected, $category);		                		 		                	
		                		$value = implode(',', $value);
		                	
		                	}else{
		                		
		                		$value = $category->getId();
		                		
		                	}
		                	
	                	}
	                		                		                		                		                	
	                    $data[] = array(
	                        'label'     => Mage::helper('core')->htmlEscape($category->getName()),
	                        'value'     => $value,	                        
	                        'count'     => isset($category_count[$category->getId()]) ? $category_count[$category->getId()] : 0,
	                        'active'	=> $active,
	                        'image' 	=> $category->getFilterImage(),
	                        'level'     => $category->getLevel(),
	                        'haschild' 	=> $category->getChildren(),                          
	                    );
	                }
	            }
	            
	            
        	}
        	
        	$tags = $this->getLayer()->getStateTags();
        	
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
            
            
        }
        return $data;
    }
    
    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_category');
        }
        return $this->_resource;
    }
    
 	protected function _renderCategoryList($category, $category_list_ids)
    {    	
    	if (array_key_exists($category->getId(), $category_list_ids)){
    		
    		array_push($this->category_list, $category_list_ids[$category->getId()]);
    		     	
	    	foreach ($category->getChildrenCategories() as $_category){
	    		$this->_renderCategoryList($_category, $category_list_ids);
	    	}
    	}
    }
    
    //remove parent category from filter
    protected function _prepareRequestValue($selected, $category){
    	$result = array();
    	$parent_ids = $category->getParentIds();
    	
    	foreach ($selected as $cat_id){
    		if (in_array($cat_id, $parent_ids)){
	    		$cat = Mage::getModel('catalog/category')->load($cat_id);
	    		if ($cat->getLevel() <  $category->getLevel()){
	    			continue; 
	    		}
    		}
    		$result[] = $cat_id;    		
    	}
    	
    	$result[] = $category->getId();
    	
    	return $result; 
    }
    
}
