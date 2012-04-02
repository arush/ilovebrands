<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setId('evogue_customersegment_segment_form');
        $this->setTitle(Mage::helper('evogue_customersegment')->__('Segment Information'));
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
