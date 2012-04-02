<?php

class Vdh_Randomquote_Block_System_Config_Form_Field_Array_Plan extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('priority', 
			array(
                'label'         => Mage::helper('core')->__('Priority').' <span class="required">*</span>',
                'style'         => 'width:20px',
                'class'         => 'required-entry validate-number'
			)
        );

        $this->addColumn('sku', 
			array(
                'label'         => Mage::helper('core')->__('Product SKU').' <span class="required">*</span>',
                'style'         => 'width:120px',
                'class'         => 'required-entry'
			)
        );
        $this->addColumn('customer_group_id', 
			array(
                'label'         => Mage::helper('core')->__('Customer Group Id').' <span class="required">*</span>',
                'style'         => 'width:20px',
                'class'         => 'required-entry validate-number'
			)
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add');
        
        parent::__construct();
        
    }
}