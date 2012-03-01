<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('current_customer_segment');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('segment_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/customersegment/newConditionHtml/form/segment_conditions_fieldset'));
        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('evogue_customersegment')->__('Conditions'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('evogue_customersegment')->__('Conditions'),
            'title' => Mage::helper('evogue_customersegment')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
