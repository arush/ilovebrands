<?php
class Evogue_CustomerSegment_Block_Adminhtml_Report_Customer_Segment extends Mage_Adminhtml_Block_Widget_Grid_Container {
    
    public function __construct() {
        $this->_blockGroup = 'evogue_customersegment';
        $this->_controller = 'adminhtml_report_customer_segment';
        $this->_headerText = Mage::helper('evogue_customersegment')->__('Customer Segment Report');
        parent::__construct();
        $this->_removeButton('add');
    }

}
