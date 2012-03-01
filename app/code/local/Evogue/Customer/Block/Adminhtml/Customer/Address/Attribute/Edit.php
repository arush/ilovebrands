<?php
class Evogue_Customer_Block_Adminhtml_Customer_Address_Attribute_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container {

    protected function _getAttribute() {
        return Mage::registry('entity_attribute');
    }

    public function __construct() {
        $this->_objectId   = 'attribute_id';
        $this->_blockGroup = 'evogue_customer';
        $this->_controller = 'adminhtml_customer_address_attribute';

        parent::__construct();

        $this->_addButton(
            'save_and_edit_button',
            array(
                'label'     => Mage::helper('evogue_customer')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save'
            ),
            100
        );

        $this->_updateButton('save', 'label', Mage::helper('evogue_customer')->__('Save Attribute'));
        if (!$this->_getAttribute()->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('evogue_customer')->__('Delete Attribute'));
        }
    }

    public function getHeaderText() {
        if ($this->_getAttribute()->getId()) {
            $label = $this->_getAttribute()->getFrontendLabel();
            if (is_array($label)) {
                // restored label
                $label = $label[0];
            }
            return Mage::helper('evogue_customer')->__('Edit Customer Address Attribute "%s"', $label);
        } else {
            return Mage::helper('evogue_customer')->__('New Customer Address Attribute');
        }
    }

    public function getValidationUrl() {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }
}
