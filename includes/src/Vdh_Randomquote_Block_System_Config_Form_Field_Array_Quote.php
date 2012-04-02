<?php

class Vdh_Randomquote_Block_System_Config_Form_Field_Array_Quote extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('quote', 
			array(
                'label'         => Mage::helper('core')->__('Quote').' <span class="required">*</span>',
                'style'         => 'width:350px',
                'class'         => 'required-entry'
			)
        );

        $this->addColumn('author', 
			array(
                'label'         => Mage::helper('core')->__('Author').' <span class="required">*</span>',
                'style'         => 'width:120px',
                'class'         => 'required-entry'
			)
        );
        $this->addColumn('caption', 
			array(
                'label'         => Mage::helper('core')->__('Caption').' <span class="required">*</span>',
                'style'         => 'width:120px',
                'class'         => 'required-entry'
			)
        );
        $this->addColumn('use_in_invite', 
			array(
                'label'         => Mage::helper('core')->__('Use in invites').' <span class="required">*</span>',
                'style'         => 'width:20px'
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