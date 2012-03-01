<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $model = Mage::registry('current_target_rule');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('evogue_targetrule')->__('General Rule Information')
        ));

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('evogue_targetrule')->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('evogue_targetrule')->__('Priority'),
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('evogue_targetrule')->__('Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('evogue_targetrule')->__('Active'),
                '0' => Mage::helper('evogue_targetrule')->__('Inactive'),
            ),
        ));
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $fieldset->addField('apply_to', 'select', array(
            'label'     => Mage::helper('evogue_targetrule')->__('Apply To'),
            'name'      => 'apply_to',
            'required'  => true,
            'options'   => Mage::getSingleton('evogue_targetrule/rule')->getAppliesToOptions(true),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'         => 'from_date',
            'label'        => Mage::helper('evogue_targetrule')->__('From Date'),
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'         => 'to_date',
            'label'        => Mage::helper('evogue_targetrule')->__('To Date'),
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('positions_limit', 'text', array(
            'name'  => 'positions_limit',
            'label' => Mage::helper('evogue_targetrule')->__('Result Limit'),
            'note'  => Mage::helper('evogue_targetrule')->__('Maximum number of products that can be matched by this Rule. Capped to 20.'),
        ));

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
