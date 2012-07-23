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
 * @since        Class available since Release 2.1
 */
 		
class GoMage_Navigation_Block_Adminhtml_Config_Form_Renderer_Navigationtype extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $after_element_html = $element->getAfterElementHtml();
        $javaScript = "
            <script type=\"text/javascript\">
                Event.observe('{$element->getHtmlId()}', 'change', function(){
                    var value = $('{$element->getHtmlId()}').value;                    
                    var elements = eval({$this->_getAssociatedElements()});
                    if (value == ".GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE."){
                    	for (var i = 0; i < elements.length; i++) {
                    		var id = '{$this->_getBasePartName($element)}' + elements[i]; 
                            if ($(id)){
                            	$(id).up('td').up('tr').show();	
    						}
                        }
                        var id = '{$this->_getBasePartName($element)}' + 'inblock_height'; 
                        if ($(id)){
                        	$(id).up('td').up('tr').hide();	
						}
    				}else if (value == ".GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK."){
    					for (var i = 0; i < elements.length; i++) {
                    		var id = '{$this->_getBasePartName($element)}' + elements[i]; 
                            if ($(id)){
                            	$(id).up('td').up('tr').hide();	
    						}
                        }
    					var id = '{$this->_getBasePartName($element)}' + 'inblock_height'; 
                        if ($(id)){
                        	$(id).up('td').up('tr').show();	
						}
    				}else{
    					for (var i = 0; i < elements.length; i++) {
                    		var id = '{$this->_getBasePartName($element)}' + elements[i]; 
                            if ($(id)){
                            	$(id).up('td').up('tr').hide();	
    						}
                        }
                        var id = '{$this->_getBasePartName($element)}' + 'inblock_height'; 
                        if ($(id)){
                        	$(id).up('td').up('tr').hide();	
						}
    				}
                });
                document.observe('dom:loaded', function() {   	
                	init_{$element->getHtmlId()}();                	
                });
                document.onreadystatechange = init_{$element->getHtmlId()};
                
                function init_{$element->getHtmlId()}() {
                	Gomage_Navigation_fireEvent($('{$element->getHtmlId()}'), 'change');
                }
            </script>";
        
        $element->setData('after_element_html', $javaScript . $after_element_html);
        
        return $element->getElementHtml();
    }
    
    
    protected function _getBasePartName($element)
    {
        return substr($element->getId(), 0, strrpos($element->getId(), 'filter_type'));
    }
    
    protected function _getAssociatedElements()
    {
        return  json_encode(array('show_image_name', 'image_align', 'image_width', 'image_height'));
    }
        
}