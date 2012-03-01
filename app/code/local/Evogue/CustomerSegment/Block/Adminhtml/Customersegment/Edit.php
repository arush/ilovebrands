<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_customersegment';
        $this->_blockGroup = 'evogue_customersegment';

        parent::__construct();
        $segment = Mage::registry('current_customer_segment');
        if ($segment) {
            if ($segment->getId()) {
                $this->_addButton('match_customers', array(
                    'label'     => Mage::helper('evogue_customersegment')->__('Refresh Segment Data'),
                    'onclick'   => 'setLocation(\'' . $this->getMatchUrl() . '\')',
                ), -1);
            }

            if ($segment->isReadonly()) {
                $this->_removeButton('save');
                $this->_removeButton('delete');
            } else {
                $this->_updateButton('save', 'label', Mage::helper('evogue_customersegment')->__('Save'));
                $this->_updateButton('delete', 'label', Mage::helper('evogue_customersegment')->__('Delete'));
                $this->_addButton('save_and_continue_edit', array(
                    'class' => 'save',
                    'label' => Mage::helper('evogue_customersegment')->__('Save and Continue Edit'),
                    'onclick'   => 'saveAndContinueEdit()',
                ), 3);

                $this->_formScripts[] = "
                    function saveAndContinueEdit() {
                        editForm.submit($('edit_form').action + 'back/edit/');
                    }";
            }
        }
    }

    public function getMatchUrl() {
        $segment = Mage::registry('current_customer_segment');
        return $this->getUrl('*/*/match', array('id'=>$segment->getId()));
    }

    public function getHeaderText() {
        $segment = Mage::registry('current_customer_segment');
        if ($segment->getSegmentId()) {
            return Mage::helper('evogue_customersegment')->__("Edit Segment '%s'", $this->htmlEscape($segment->getName()));
        }
        else {
            return Mage::helper('evogue_customersegment')->__('New Segment');
        }
    }
}
