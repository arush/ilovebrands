<?php

class Vdh_Customerurl_Block_System_Config_Form_Field_Array_Mapping extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('url', 
			array(
                'label'         => Mage::helper('core')->__('Friendly URL').' <span class="required">*</span>',
                'style'         => 'width:200px',
                'class'         => 'required-entry'
			)
        );

        $this->addColumn('redirect', 
			array(
                'label'         => Mage::helper('core')->__('Original URL').' <span class="required">*</span>',
                'style'         => 'width:200px',
                'class'         => 'required-entry'
			)
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add');
        
        parent::__construct();
        
    }
}