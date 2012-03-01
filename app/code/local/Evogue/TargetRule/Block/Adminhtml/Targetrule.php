<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule extends Mage_Adminhtml_Block_Widget_Grid_Container {
    
    public function __construct() {
        $this->_controller = 'adminhtml_targetrule';
        $this->_blockGroup = 'evogue_targetrule';
        $this->_headerText = Mage::helper('evogue_targetrule')->__('Manage Product Rules');
        $this->_addButtonLabel = Mage::helper('evogue_targetrule')->__('Add Rule');
        parent::__construct();
    }

}
