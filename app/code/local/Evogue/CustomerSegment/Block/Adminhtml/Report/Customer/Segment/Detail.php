<?php
class Evogue_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Detail
    extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'evogue_customersegment';
        $this->_controller = 'adminhtml_report_customer_segment_detail';
        $this->_headerText = (!$this->htmlEscape($this->getCustomerSegment()->getName()))
            ? Mage::helper('evogue_customersegment')->__('Customer Segments Report')
            : Mage::helper('evogue_customersegment')->__('Customer Segment Report \'%s\'',$this->htmlEscape($this->getCustomerSegment()->getName()));

        parent::__construct();
        $this->_removeButton('add');
        $this->addButton('back', array(
            'label'     => Mage::helper('evogue_customersegment')->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
        $this->addButton('refresh', array(
            'label'     => Mage::helper('evogue_customersegment')->__('Refresh Segment Data'),
            'onclick'   => 'setLocation(\'' . $this->getRefreshUrl() .'\')',
        ));
    }

    public function getRefreshUrl() {
        return $this->getUrl('*/*/refresh', array('_current' => true));
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/segment');
    }

    public function getCustomerSegment() {
        return Mage::registry('current_customer_segment');
    }

    public function getWebsites() {
        return Mage::app()->getWebsites();
    }

}
