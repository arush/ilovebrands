<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $model  = Mage::registry('current_target_rule');

        $form   = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset   = $form->addFieldset('conditions_fieldset', array(
            'legend' => Mage::helper('evogue_targetrule')->__('Product Match Conditions (leave blank for matching all products)'))
        );
        $newCondUrl = $this->getUrl('*/targetrule/newConditionHtml/', array(
            'form'  => $fieldset->getHtmlId()
        ));
        $renderer   = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('evogue/targetrule/edit/conditions/fieldset.phtml')
            ->setNewChildUrl($newCondUrl);
        $fieldset->setRenderer($renderer);

        $element    = $fieldset->addField('conditions', 'text', array(
            'name'      => 'conditions',
            'required'  => true,
        ));

        $element->setRule($model);
        $element->setRenderer(Mage::getBlockSingleton('evogue_targetrule/adminhtml_rule_conditions'));

        $model->getConditions()->setJsFormObject($fieldset->getHtmlId());
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
