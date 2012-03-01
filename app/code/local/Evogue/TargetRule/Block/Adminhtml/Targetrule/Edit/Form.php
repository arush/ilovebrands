<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct()
    {
        parent::__construct();
        $this->setId('evogue_targetrule_form');
        $this->setTitle(Mage::helper('evogue_targetrule')->__('Rule Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form',
            'action' => Mage::helper('adminhtml')->getUrl('*/*/save'), 'method' => 'post'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }


}
