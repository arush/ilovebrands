<?php
class Evogue_Customer_Block_Adminhtml_Customer_Attribute_Edit_Js
    extends Mage_Adminhtml_Block_Template {

    public function getValidateFiltersJson() {
        return Mage::helper('core')->jsonEncode(Mage::helper('evogue_customer')->getAttributeValidateFilters());
    }


    public function getFilteTypesJson() {
        return Mage::helper('core')->jsonEncode(Mage::helper('evogue_customer')->getAttributeFilterTypes());
    }

    public function getAttributeInputTypes() {
        return Mage::helper('evogue_customer')->getAttributeInputTypes();
    }
}
