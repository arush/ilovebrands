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

class GoMage_Feed_Block_Adminhtml_Attributes_Edit_Tab_Data extends Mage_Adminhtml_Block_Template
{
	
	protected $attribute;
	protected $catalog_attribute;
	protected $options;
	
	public function getAttribute(){
		
		if(Mage::registry('gomage_custom_attribute')){
        	return Mage::registry('gomage_custom_attribute');
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
			
			$options['Product ID'] = array('code'=>"entity_id", 'label' => "Product ID");
	    	$options['Is In Stock'] = array('code'=>"is_in_stock" , 'label' =>  "Is In Stock");
	    	$options['Qty'] = array('code'=>"qty" , 'label' =>  "Qty");
	    	$options['Category ID'] = array('code'=>"category_id", 'label' =>  "Category ID");
	    	$options['Final Price'] = array('code'=>"final_price", 'label' =>  "Final Price");
	    	$options['Product Type'] = array('code'=>"product_type" , 'label' =>  "Product Type");
						
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
	public function getAttributeValueField($code = '', $i, $j, $current = ''){
		
		$html = '';
				
		if($code){

			$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
	    		
			if(($attribute && ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')) ||
			   ($code == 'product_type')){
	        	
	        	$options = array();
	        	
			   if ($code == 'product_type'){
	        		$attribute_options = Mage_Catalog_Model_Product_Type::getOptions();
	        	}else{
	        		$attribute_options = $attribute->getSource()->getAllOptions();
	        	}
			
				foreach($attribute_options as $option){
					
					extract($option);
					
					$selected = '';
					
					if($current == $value){
						$selected = 'selected="selected"';
					}
					
					$options[] = "<option value=\"{$value}\" {$selected}>{$label}</option>";
					
				}
				
				$html = ('<select style="width: 100%; border: 0pt none; padding: 0pt;" name="option['.$i.'][condition]['.$j.'][value]">'.implode('', $options).'</select>');
			
	        	
	        }
	        
		}
		
		if($html){
			
			return $html;
			
		}else{
			
			return ('<input style="width:100%;border:0;padding:0;" type="text" class="input-text" name="option['.$i.'][condition]['.$j.'][value]" value="'.$current.'"/>');
			
		}
	}
	
	public function getAttributeSelect($row_id, $condition_id,  $current = null, $active = true, $custom_name = '', $disable_action = false, $required = false, $empty_text='Please Select'){
		
		$selected = '';
		
		if(is_null($current)){
			$selected = 'selected="selected"';
		}
		
		$label = $this->__($empty_text);
		
		$options = array("<option value='' {$selected} >{$label}</option>");
		
		foreach($this->getAttributeOptionsArray() as $attribute){
			
			extract($attribute);
			
			$selected = '';
			
			if($code == $current){
				$selected = 'selected="selected"';
			}
			
			$options[] = "<option value=\"{$code}\" {$selected}>{$label}</option>";
			
		}
		
		
		
		return '<select '.( $required ? 'class="required-entry"' : '').' ' .($disable_action ? '' : ' onchange="getAttributeValueField(this.value, $(\'condition-'.$row_id.'-'.$condition_id.'-value-wraper\'), $(\'condition-'.$row_id.'-'.$condition_id.'-condition-wraper\'), \'option['.$row_id.'][condition]['.$condition_id.'][value]\', \'option['.$row_id.'][condition]['.$condition_id.'][condition]\')"' ).' style="width:150px;display:'.($active ? 'block' : 'none').'" name="'.($custom_name ? $custom_name : 'option['.$row_id.'][condition]['.$condition_id.'][attribute_code]').'">'.implode('', $options).'</select>';
		
	}
	public function getConditionSelect($row_id, $condition_id, $current = null, $custom_name = '', $attribute_code = ''){
		
		if($attribute_code){
			$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attribute_code);
    		
    		if( ($attribute && ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')) ||
    			($attribute_code == 'product_type') ){
	        	
				$options = array(
				'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.$this->__('equal').'</option>',
				'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.$this->__('not equal').'</option>',
				);
	        	
	        }
		}
		
		if(empty($options)){
		
		$options = array(
			'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.$this->__('equal').'</option>',
			'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.$this->__('not equal').'</option>',
			'<option '.($current == 'gt' ? 'selected="selected"' : '').' value="gt">'.$this->__('greater than').'</option>',
			'<option '.($current == 'lt' ? 'selected="selected"' : '').' value="lt">'.$this->__('less than').'</option>',
			'<option '.($current == 'gteq' ? 'selected="selected"' : '').' value="gteq">'.$this->__('greater than or equal to').'</option>',
			'<option '.($current == 'lteq' ? 'selected="selected"' : '').' value="lteq">'.$this->__('less than or equal to').'</option>',
			'<option '.($current == 'like' ? 'selected="selected"' : '').' value="like">'.$this->__('like').'</option>',
			'<option '.($current == 'nlike' ? 'selected="selected"' : '').' value="nlike">'.$this->__('not like').'</option>',
		);
		
		}
		
		return '<select style="width:120px" name="' . ($custom_name ? $custom_name : 'option['.$row_id.'][condition]['.$condition_id.'][condition]' ) . '">'.implode('', $options).'</select>';
	}
    
  
}