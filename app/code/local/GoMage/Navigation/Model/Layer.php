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
	
	class GoMage_Navigation_Model_Layer extends Mage_Catalog_Model_Layer{
		
		const FILTER_TYPE_DEFAULT		    = 0;
		const FILTER_TYPE_IMAGE			    = 1;
		const FILTER_TYPE_DROPDOWN		    = 2;
		const FILTER_TYPE_INPUT			    = 3;
		const FILTER_TYPE_SLIDER		    = 4;
		const FILTER_TYPE_SLIDER_INPUT	    = 5;
		const FILTER_TYPE_PLAIN	            = 6;
		const FILTER_TYPE_FOLDING	        = 7;
		const FILTER_TYPE_DEFAULT_PRO       = 8;
		const FILTER_TYPE_DEFAULT_INBLOCK   = 9;
		const FILTER_TYPE_INPUT_SLIDER	    = 10;
		const FILTER_TYPE_ACCORDION	    	= 11;
		
		public function prepareProductCollection($collection){
			
			parent::prepareProductCollection($collection);
			
			$collection->getSelect()->group('e.entity_id');
			
			return $this;
			
		}
		
		protected function _prepareAttributeCollection($collection){
			
			$collection = parent::_prepareAttributeCollection($collection);
			
	        $collection->addIsFilterableFilter();
	        
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
		
	}