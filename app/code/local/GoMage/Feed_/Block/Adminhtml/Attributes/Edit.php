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

class GoMage_Feed_Block_Adminhtml_Attributes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct(){
    	
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'gomage_feed';
        $this->_controller = 'adminhtml_attributes';
        
        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));
		
		$model = Mage::registry('gomage_custom_attribute');
		
        $this->_addButton('saveandcontinue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    public function getHeaderText(){
    	
        if( Mage::registry('gomage_custom_attribute') && Mage::registry('gomage_custom_attribute')->getId() ) {
            return $this->__("Edit \"%s\"", $this->htmlEscape(Mage::registry('gomage_custom_attribute')->getName()));
        } else {
            return $this->__('Add Dynamic Attribute');
        }
    }
}