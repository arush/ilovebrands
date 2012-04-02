<?php
class Vdh_Popup_Block_Url extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function __construct() {
        $this->addColumn('url', 
			array(
                'label'         => Mage::helper('popup')->__('Url'),
                'style'         => 'width:320px',
                'class'         => 'required-entry'
			)
        );
        $this->addColumn('sort_order', 
			array(
                'label'         => Mage::helper('popup')->__('Sort order'),
                'style'         => 'width:30px',
                'class'         => 'required-entry'
			)
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('popup')->__('Add');
        
        parent::__construct();
        
    }
}