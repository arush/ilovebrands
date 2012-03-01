<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    protected $_blockGroup = 'evogue_targetrule';
    protected $_controller = 'adminhtml_targetrule';

    public function __construct()
    {
        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('evogue_targetrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('evogue_targetrule')->__('Delete Rule'));
        $this->_addButton('save_and_continue_edit', array(
            'class' => 'save',
            'label' => Mage::helper('evogue_targetrule')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
        ), 3);

        $this->_formScripts[] = '
            function saveAndContinueEdit() {
                editForm.submit($(\'edit_form\').action + \'back/edit/\');
            }';
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('current_target_rule');
        if ($rule && $rule->getRuleId()) {
            return Mage::helper('evogue_targetrule')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        }
        else {
            return Mage::helper('evogue_targetrule')->__('New Rule');
        }
    }

}
