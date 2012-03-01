<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment extends Mage_Adminhtml_Block_Widget_Grid_Container {
    
    public function __construct() {
        $this->_controller = 'adminhtml_customersegment';
        $this->_blockGroup = 'evogue_customersegment';
        $this->_headerText = Mage::helper('evogue_customersegment')->__('Manage Segments');
        $this->_addButtonLabel = Mage::helper('evogue_customersegment')->__('Add Segment');
        parent::__construct();    
    }

}
