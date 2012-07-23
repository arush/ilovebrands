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

class GoMage_Navigation_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Main extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        $helper = Mage::helper('gomage_navigation');
        
        $form = $this->getForm();
        
        $fieldset = $form->addFieldset('advanced_navigation_fieldset', array('legend'=>Mage::helper('catalog')->__('Advanced Navigation Properties')));
        
        
        $field = $fieldset->addField('filter_type', 'select', array(
            'name' => 'filter_type',
            'label' => Mage::helper('catalog')->__('Filter Type'),
            'title' => Mage::helper('catalog')->__('Filter Type'),
            'values'=>Mage::getModel('gomage_navigation/adminhtml_system_config_source_filter_type_attribute')->toOptionArray(),
        ));
        
        $field->setData('after_element_html', '<script type="text/javascript">
        	
        	is_price = false;
        	
        	if($("frontend_input").value != "price"){
				
				var options = $("filter_type").select("option");
    			
    			for(var i = 0; i < options.length; i++){
    				
    				e = options[i];
    				
    				if(e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER.'){
    					
    					e.parentNode.removeChild(e);
    					
    				}
    			};
			
        	}else{
        		is_price = true;
        	}
        	
        	Event.observe($("frontend_input"), "change", function(e){
        		
        		if(this.value == "price"){
        			
        			if(!is_price){
	        			
	        			var option = document.createElement("option");
						option.value = "'.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT.'";
						option.innerHTML = "'.$helper->__('Input').'";
						
						$("filter_type").appendChild(option);
						
						var option = document.createElement("option");
						option.value = "'.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER.'";
						option.innerHTML = "'.$helper->__('Slider').'";
						
						$("filter_type").appendChild(option);
						
						var option = document.createElement("option");
						option.value = "'.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT.'";
						option.innerHTML = "'.$helper->__('Slider and Input').'";
						
						$("filter_type").appendChild(option);
												
						var option = document.createElement("option");
						option.value = "'.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER.'";
						option.innerHTML = "'.$helper->__('Input and Slider').'";
						
						$("filter_type").appendChild(option);
	        			
	        			is_price = true;
	        			
    				}
    				
        		}else{
        			
        			is_price = false;
        			
        			var options = $("filter_type").select("option");
        			
        			for(var i = 0; i < options.length; i++){
        				
        				e = options[i];
        				
        				if(e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT.' || e.value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER. '){
        					
        					e.parentNode.removeChild(e);
        					
        				}
        			};
        			
        		}
        		
        	});
        	
        	Event.observe("filter_type", "change", function(){
                    var value = $("filter_type").value;                    
                    var elements = eval('.$this->_getAssociatedElements().');
                    if (value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE.'){
                    	for (var i = 0; i < elements.length; i++) {
                    		var id = elements[i]; 
                            if ($(id)){
                            	$(id).up("td").up("tr").show();	
    						}
                        }
                        var id = "inblock_height"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").hide();	
						}
						var id = "filter_button"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").hide();	
						}
    				}else if (value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK.'){
    					for (var i = 0; i < elements.length; i++) {
                    		var id = elements[i]; 
                            if ($(id)){
                            	$(id).up("td").up("tr").hide();	
    						}
                        }
                        var id = "inblock_height"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").show();	
						}
						var id = "filter_button"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").hide();	
						}
    				}else if (value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT.' || value == '.GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER.'){
    					for (var i = 0; i < elements.length; i++) {
                    		var id = elements[i]; 
                            if ($(id)){
                            	$(id).up("td").up("tr").hide();	
    						}
                        }
                        var id = "inblock_height"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").hide();	
						}
						var id = "filter_button"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").show();	
						}
    				}else{
    					for (var i = 0; i < elements.length; i++) {
                    		var id = elements[i]; 
                            if ($(id)){
                            	$(id).up("td").up("tr").hide();	
    						}
                        }
                        var id = "inblock_height"; 
                        if ($(id)){
                            $(id).up("td").up("tr").hide();	
    					}
    					var id = "filter_button"; 
                        if ($(id)){
                        	$(id).up("td").up("tr").hide();	
						}
        			}
                });
                
                Event.observe(window, "load", function() {
						init_filter_type();	
					}
				);
                                
                function init_filter_type() {
                	Gomage_Navigation_fireEvent($("filter_type"), "change");
                }
        	
        </script>');
        
        $fieldset->addField('inblock_height', 'text', array(
            'name' => 'inblock_height',
            'label' => Mage::helper('catalog')->__('Block Height, px'),
            'title' => Mage::helper('catalog')->__('Block Height, px'),
            'class' => 'gomage-validate-number',
        ));
        
        $fieldset->addField('filter_button', 'select', array(
            'name' => 'filter_button',
            'label' => Mage::helper('catalog')->__('Show Filter Button '),
            'title' => Mage::helper('catalog')->__('Show Filter Button '),
            'values'=>array(
                	0 => $this->__('No'),
                	1 => $this->__('Yes'),
                ),
            
        ));
        
        $field = $fieldset->addField('is_ajax', 'select', array(
            'name' => 'is_ajax',
            'label' => Mage::helper('catalog')->__('Use Ajax'),
            'title' => Mage::helper('catalog')->__('Use Ajax'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));

        $field->setValue(0);
        
        
        $fieldset->addField('show_minimized', 'select', array(
            'name' => 'show_minimized',
            'label' => Mage::helper('catalog')->__('Show Minimized'),
            'title' => Mage::helper('catalog')->__('Show Minimized'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));
        
        $field = $fieldset->addField('show_checkbox', 'select', array(
            'name' => 'show_checkbox',
            'label' => Mage::helper('catalog')->__('Show Checkboxes'),
            'title' => Mage::helper('catalog')->__('Show Checkboxes'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));
        
        $field->setValue(0);
        
        $field = $fieldset->addField('show_image_name', 'select', array(
            'name' => 'show_image_name',
            'label' => Mage::helper('catalog')->__('Show Image Name'),
            'title' => Mage::helper('catalog')->__('Show Image Name'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));
        
        $field->setValue(0);
        
        $fieldset->addField('image_align', 'select', array(
            'name' => 'image_align',
            'label' => Mage::helper('catalog')->__('Image Alignment'),
            'title' => Mage::helper('catalog')->__('Image Alignment'),
            'values'=> Mage::getModel('gomage_navigation/adminhtml_system_config_source_filter_image_align')->toOptionArray(),
        ));
        
        $fieldset->addField('image_width', 'text', array(
            'name' => 'image_width',
            'label' => Mage::helper('catalog')->__('Image Width, px'),
            'title' => Mage::helper('catalog')->__('Image Width, px'),
            'class' => 'gomage-validate-number',
        ));
        
        $fieldset->addField('image_height', 'text', array(
            'name' => 'image_height',
            'label' => Mage::helper('catalog')->__('Image Height, px'),
            'title' => Mage::helper('catalog')->__('Image Height, px'),
            'class' => 'gomage-validate-number',
        ));
        
        $fieldset->addField('visible_options', 'text', array(
            'name' => 'visible_options',
            'label' => Mage::helper('catalog')->__('Visible Options per Attribute'),
            'title' => Mage::helper('catalog')->__('Visible Options per Attribute'),
            'class' => 'gomage-validate-number',
        ));
        
        $field= $fieldset->addField('show_help', 'select', array(
            'name' => 'show_help',
            'label' => Mage::helper('catalog')->__('Show Help Icon'),
            'title' => Mage::helper('catalog')->__('Show Help Icon'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));
        
        $field->setValue(0);
        
        $fieldset->addField('popup_width', 'text', array(
            'name' => 'popup_width',
            'label' => Mage::helper('catalog')->__('Popup Width, px'),
            'title' => Mage::helper('catalog')->__('Popup Width, px'),
            'class' => 'gomage-validate-number',
        ));
        $fieldset->addField('popup_height', 'text', array(
            'name' => 'popup_height',
            'label' => Mage::helper('catalog')->__('Popup Height, px'),
            'title' => Mage::helper('catalog')->__('Popup Height, px'),
            'class' => 'gomage-validate-number',
        ));
        
        $field = $fieldset->addField('filter_reset', 'select', array(
            'name' => 'filter_reset',
            'label' => Mage::helper('catalog')->__('Show Reset Link'),
            'title' => Mage::helper('catalog')->__('Show Reset Link'),
            'values'=>array(
            	0 => $this->__('No'),
            	1 => $this->__('Yes'),
            ),
        ));
        
        $field->setValue(0);
        

        return $this;
    }
    
    protected function _getAssociatedElements()
    {
        return  json_encode(array('show_image_name', 'image_align', 'image_width', 'image_height'));
    }
}
