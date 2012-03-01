<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('targetrule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('evogue_targetrule')->__('Product Rule Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('evogue_targetrule')->__('Rule Information'),
            'content'   => $this->getLayout()->createBlock('evogue_targetrule/adminhtml_targetrule_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('evogue_targetrule')->__('Products to Match'),
            'content'   => $this->getLayout()->createBlock('evogue_targetrule/adminhtml_targetrule_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('targeted_products', array(
            'label'     => Mage::helper('evogue_targetrule')->__('Products to Display'),
            'content'   => $this->getLayout()->createBlock('evogue_targetrule/adminhtml_targetrule_edit_tab_actions')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
