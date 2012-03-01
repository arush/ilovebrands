<?php
class Evogue_Customer_Block_Adminhtml_Customer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'evogue_customer';
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_headerText = Mage::helper('evogue_customer')->__('Manage Customer Attributes');
        $this->_addButtonLabel = Mage::helper('evogue_customer')->__('Add New Attribute');
        parent::__construct();
    }
}
