<?php

class Pod1_Shutl_Block_System_Config_Form_Field_Array_Message extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('error_code', 
			array(
                'label'         => Mage::helper('core')->__('Code').' <span class="required">*</span>',
                'style'         => 'width:80px',
                'class'         => 'required-entry validate-number'
			)
        );

        $this->addColumn('error_message', 
			array(
                'label'         => Mage::helper('core')->__('Message').' <span class="required">*</span>',
                'style'         => 'width:200px',
                'class'         => 'required-entry'
			)
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add');
        
        parent::__construct();
        
    }
}