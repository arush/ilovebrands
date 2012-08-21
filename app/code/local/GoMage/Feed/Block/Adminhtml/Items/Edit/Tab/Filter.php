<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Filter extends Mage_Adminhtml_Block_Template
{
	
	protected $attribute_collection;
	protected $options;
	
	public function getFeed(){
		
		if(Mage::registry('gomage_feed')){
        	return Mage::registry('gomage_feed');
        }else{
        	return  new Varien_Object();
        }
		
	}
	
	public function getAttributeCollection(){
		if(is_null($this->attribute_collection)){
			
			$this->attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')
				->setItemObjectClass('catalog/resource_eav_attribute')
				->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());
			
		}
		return $this->attribute_collection;
	}
	
	public function getAttributeOptionsArray(){
		
		if(is_null($this->options)){
			
			$options = array();
			
			$options['Category'] = array('code' => "category_id", 'label' => "Category");
			$options['Qty'] = array('code'=>"qty" , 'label' => "Qty");
			$options['Product Type'] = array('code'=>"product_type" , 'label' => "Product Type");
			
			foreach($this->getAttributeCollection() as $attribute){
				if($attribute->getFrontendLabel()){
				$options[$attribute->getFrontendLabel()] = array('code'=>$attribute->getAttributeCode(), 'label'=>($attribute->getFrontendLabel() ? $attribute->getFrontendLabel() : $attribute->getAttributeCode()));
				}
				
			}
			
			ksort($options);
			
			$this->options = $options;
			
		}
		
		return $this->options;
		
	}
	public static function getAttributeValueField($code = '', $name = '', $current = '', $store_id = null){
		
		if($code){
			
			$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
    		
    		if($attribute && ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')){
	        	
	        	$options = array();
			
				foreach($attribute->getSource()->getAllOptions() as $option){
					
					extract($option);
					
					$selected = '';
					
					if($current == $value){
						$selected = 'selected="selected"';
					}
					
					$options[] = "<option value=\"{$value}\" {$selected}>{$label}</option>";
					
				}
				
				return ('<select style="width: 100%; border: 0pt none; padding: 0pt;" name="'.$name.'">'.implode('', $options).'</select>');
			
    		}
	        elseif ($code == 'category_id'){
	            $options = array();
	            $options[] = "<option value=\"\"></option>";
	            	            	            	            
	            if (!$store_id) $store_id = Mage::app()->getStore()->getId();
	            
	            $store = Mage::getModel('core/store')->load($store_id);
	            
	            $categoryes = Mage::getModel('catalog/category')->load($store->getRootCategoryId())->getAllChildren(true);
	            
	            foreach($categoryes as $cat_id){

	                if ($cat_id == $store->getRootCategoryId()) continue;
	                
	                $category = Mage::getModel('catalog/category')->load($cat_id);
	                
	                $selected = '';
					
					if($current == $category->getId()){
						$selected = 'selected="selected"';
					}
					
					$options[] = "<option value=\"{$category->getId()}\" {$selected}>{$category->getName()}</option>";
					
				}
	            
	            return ('<select style="width: 100%; border: 0pt none; padding: 0pt;" name="'.$name.'">'.implode('', $options).'</select>');

	        }elseif ($code == 'product_type'){  
	        	$options = array();
	            

	            foreach(Mage_Catalog_Model_Product_Type::getOptions() as $product_type){
	                
	                $selected = '';
					
					if($current == $product_type['value']){
						$selected = 'selected="selected"';
					}
					
					$options[] = "<option value=\"{$product_type['value']}\" {$selected}>{$product_type['label']}</option>";
					
				}
	            
	            return ('<select style="width: 100%; border: 0pt none; padding: 0pt;" name="'.$name.'">'.implode('', $options).'</select>');
	            
	        }else{
	        	
	        	return ('<input style="width:100%;border:0;padding:0;" type="text" class="input-text" name="'.$name.'" value="'.$current.'"/>');
	        	
	        }
			
		}
		
	}
	
	public function getAttributeSelect($i, $current = null, $active = true, $additional = ''){
		
		$options = array();
		
		foreach($this->getAttributeOptionsArray() as $attribute){
			
			extract($attribute);
			
			$selected = '';
			
			if($code == $current){
				$selected = 'selected="selected"';
			}
			
			$options[] = "<option value=\"{$code}\" {$selected}>{$label}</option>";
			
		}
		
		
		
		return '<select '.$additional.' style="width:260px;display:'.($active ? 'block' : 'none').'" id="filter-'.$i.'-attribute-code" name="filter['.$i.'][attribute_code]">'.implode('', $options).'</select>';
		
	}
	
	public static function getConditionSelect($i, $current = null){
		
		$options = array(
			'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.Mage::helper('gomage_feed')->__('equal').'</option>',
			'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.Mage::helper('gomage_feed')->__('not equal').'</option>',
			'<option '.($current == 'gt' ? 'selected="selected"' : '').' value="gt">'.Mage::helper('gomage_feed')->__('greater than').'</option>',
			'<option '.($current == 'lt' ? 'selected="selected"' : '').' value="lt">'.Mage::helper('gomage_feed')->__('less than').'</option>',
			'<option '.($current == 'gteq' ? 'selected="selected"' : '').' value="gteq">'.Mage::helper('gomage_feed')->__('greater than or equal to').'</option>',
			'<option '.($current == 'lteq' ? 'selected="selected"' : '').' value="lteq">'.Mage::helper('gomage_feed')->__('less than or equal to').'</option>',
			'<option '.($current == 'like' ? 'selected="selected"' : '').' value="like">'.Mage::helper('gomage_feed')->__('like').'</option>',
			'<option '.($current == 'nlike' ? 'selected="selected"' : '').' value="nlike">'.Mage::helper('gomage_feed')->__('not like').'</option>',
		    '<option '.($current == 'nin' ? 'selected="selected"' : '').' value="nin">'.Mage::helper('gomage_feed')->__('xor').'</option>',
		);
		
		return '<select style="width:160px" id="filter-'.$i.'-condition" name="filter['.$i.'][condition]">'.implode('', $options).'</select>';
	}
    
	public static function getConditionSelectLight($i, $current = null){
		
		$options = array(
			'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.Mage::helper('gomage_feed')->__('equal').'</option>',
			'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.Mage::helper('gomage_feed')->__('not equal').'</option>',			
		);
		
		return '<select style="width:160px" id="filter-'.$i.'-condition" name="filter['.$i.'][condition]">'.implode('', $options).'</select>';
	}
  
}