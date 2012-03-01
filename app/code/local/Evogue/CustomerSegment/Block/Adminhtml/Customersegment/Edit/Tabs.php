<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('evogue_customersegment_segment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('evogue_customersegment')->__('Segment Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('general_section', array(
            'label'     => Mage::helper('evogue_customersegment')->__('General Properties'),
            'title'     => Mage::helper('evogue_customersegment')->__('General Properties'),
            'content'   => $this->getLayout()->createBlock('evogue_customersegment/adminhtml_customersegment_edit_tab_general')->toHtml(),
            'active'    => true
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('evogue_customersegment')->__('Conditions'),
            'title'     => Mage::helper('evogue_customersegment')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('evogue_customersegment/adminhtml_customersegment_edit_tab_conditions')->toHtml(),
        ));

        $segment = Mage::registry('current_customer_segment');
        if ($segment && $segment->getId()) {
            $customersQty = Mage::getModel('evogue_customersegment/segment')->getResource()
                ->getSegmentCustomersQty($segment->getId());
            $this->addTab('customers_tab', array(
                'label' => Mage::helper('evogue_customersegment')->__('Matched Customers (%d)', $customersQty),
                'url'   => $this->getUrl('*/report_customer_customersegment/customerGrid',
                    array('segment_id' => $segment->getId())),
                'class' => 'ajax',
            ));
        }

        return parent::_beforeToHtml();
    }

}
