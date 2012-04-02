<?php
class Evogue_Customer_Block_Adminhtml_Customer_Address_Attribute_Edit_Tab_Options
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    
    public function getTabLabel() {
        return Mage::helper('evogue_customer')->__('Manage Label / Options');
    }

    public function getTabTitle() {
        return Mage::helper('evogue_customer')->__('Properties');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
}
