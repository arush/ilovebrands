<?php

class Vdh_Randomquote_Block_System_Config_Form_Field_Array_Style extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('text',
			array(
                'label'         => Mage::helper('core')->__('Style').' <span class="required">*</span>',
                'style'         => 'width:350px',
                'class'         => 'required-entry'
			)
        );

        $this->addColumn('order', 
			array(
                'label'         => Mage::helper('core')->__('Sort Order').' <span class="required">*</span>',
                'style'         => 'width:20px',
                'class'         => 'required-entry validate-number'
			)
        );



        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add');
        
        parent::__construct();
        
    }
}