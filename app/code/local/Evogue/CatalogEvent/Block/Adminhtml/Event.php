<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_event';
        $this->_blockGroup = 'evogue_catalogevent';
        $this->_headerText = Mage::helper('evogue_catalogevent')->__('Manage Catalog Events');
        $this->_addButtonLabel = Mage::helper('evogue_catalogevent')->__('Add Catalog Event');
        parent::__construct();
    }

    public function getHeaderCssClass() {
        return 'icon-head head-catalogevent';
    }
}
