<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model  = Mage::registry('current_target_rule');
        $form   = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset   = $form->addFieldset('actions_fieldset', array(
            'legend' => Mage::helper('evogue_targetrule')->__('Product Result Conditions (leave blank for matching all products)'))
        );
        $newCondUrl = $this->getUrl('*/targetrule/newActionsHtml/', array(
            'form'  => $fieldset->getHtmlId()
        ));
        $renderer   = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('evogue/targetrule/edit/conditions/fieldset.phtml')
            ->setNewChildUrl($newCondUrl);
        $fieldset->setRenderer($renderer);

        $element    = $fieldset->addField('actions', 'text', array(
            'name'      => 'actions',
            'required'  => true
        ));
        $element->setRule($model);
        $element->setRenderer(Mage::getBlockSingleton('evogue_targetrule/adminhtml_actions_conditions'));

        $model->getActions()->setJsFormObject($fieldset->getHtmlId());
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
