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
 	
	class GoMage_Navigation_Model_Search_Layer extends GoMage_Navigation_Model_Layer{
		
		/**
	     * Get current layer product collection
	     *
	     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	     */
	    public function getProductCollection()
	    {
	        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
	            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
	        }
	        else {
	        	
	        	if(!($collection = Mage::getResourceModel('catalogsearch/fulltext_collection'))){
	            	$engine = Mage::helper('catalogsearch')->getEngine();
	            	$collection = $engine->getResultCollection();
	            }
	            
	            $this->prepareProductCollection($collection);
	            
	            $collection->getSelect()->group('e.entity_id');
	            
	            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
	        }
	        

	        return $collection;
	    }
		/**
	     * Prepare product collection
	     *
	     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
	     * @return Mage_Catalog_Model_Layer
	     */
	    public function prepareProductCollection($collection)
	    {
	        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
	            ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
	            ->setStore(Mage::app()->getStore())
	            ->addMinimalPrice()
	            ->addFinalPrice()
	            ->addTaxPercents()
	            ->addStoreFilter()
	            ->addUrlRewrite();

	        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
	        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
	        return $this;
	    }
		    
	    /**
	     * Get layer state key
	     *
	     * @return string
	     */
	    public function getStateKey()
	    {
	        if ($this->_stateKey === null) {
	            $this->_stateKey = 'Q_'.Mage::helper('catalogsearch')->getQuery()->getId()
	                .'_'.parent::getStateKey();
	        }
	        return $this->_stateKey;
	    }

	    /**
	     * Get default tags for current layer state
	     *
	     * @param   array $additionalTags
	     * @return  array
	     */
	    public function getStateTags(array $additionalTags = array())
	    {
	        $additionalTags = parent::getStateTags($additionalTags);
	        $additionalTags[] = Mage_CatalogSearch_Model_Query::CACHE_TAG;
	        return $additionalTags;
	    }

	    /**
	     * Add filters to attribute collection
	     *
	     * @param   Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection $collection
	     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection
	     */
	    protected function _prepareAttributeCollection($collection)
	    {
	        $collection->addIsFilterableInSearchFilter();
	        
	        $tableAlias = 'gomage_nav_attr';
	        
	        $collection->getSelect()->joinLeft(
	            array($tableAlias => Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute')),
	            "`main_table`.`attribute_id` = `{$tableAlias}`.`attribute_id`",
	            array('filter_type', 'image_align', 'image_width', 'image_height', 'show_minimized', 'show_image_name', 'visible_options', 'show_help', 'show_checkbox', 'popup_width', 'popup_height', 'filter_reset', 'is_ajax', 'inblock_height', 'filter_button')
	        );
	        
	        $tableAliasStore = 'gomage_nav_attr_store';
	        
	        $collection->getSelect()->joinLeft(
	        	array($tableAliasStore => Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute_store')),
	            "`main_table`.`attribute_id` = `{$tableAliasStore}`.`attribute_id` and `{$tableAliasStore}`.store_id = " . Mage::app()->getStore()->getStoreId(),
	        	array('popup_text')
	        );
	        
	        
	        $connection = Mage::getSingleton('core/resource')->getConnection('read');
	        $table = Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute_option');
	        
	        foreach($collection as $attribute){
	        	
	        	$option_images = array();
	        	
	        	$attribute_id = $attribute->getId();
	        	
				$_option_images = $connection->fetchAll("SELECT `option_id`, `filename` FROM {$table} WHERE `attribute_id` = {$attribute_id};");
				
				foreach($_option_images as $imageInfo){
					
					$option_images[$imageInfo['option_id']] = $imageInfo['filename'];
					
				}
				
				$attribute->setOptionImages($option_images);
				
	        	
	        }
	        
	        
	        return $collection;
	    }

	    /**
	     * Prepare attribute for use in layered navigation
	     *
	     * @param   Mage_Eav_Model_Entity_Attribute $attribute
	     * @return  Mage_Eav_Model_Entity_Attribute
	     */
	    protected function _prepareAttribute($attribute)
	    {
	        $attribute = parent::_prepareAttribute($attribute);
	        $attribute->setIsFilterable(Mage_Catalog_Model_Layer_Filter_Attribute::OPTIONS_ONLY_WITH_RESULTS);
	        return $attribute;
	    }
		
	}