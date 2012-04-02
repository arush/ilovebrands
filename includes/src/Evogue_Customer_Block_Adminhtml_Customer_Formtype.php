<?php
class Evogue_Customer_Block_Adminhtml_Customer_Formtype extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'evogue_customer';
        $this->_controller = 'adminhtml_customer_formtype';
        $this->_headerText = Mage::helper('evogue_customer')->__('Manage Form Types');

        parent::__construct();

        $this->_updateButton('add', 'label', Mage::helper('evogue_customer')->__('New Form Type'));
    }
}
