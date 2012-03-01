<?php
class Evogue_Customer_Block_Adminhtml_Customer_Attribute_Edit_Tab_Main
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _prepareLayout() {
        $result = parent::_prepareLayout();
        $renderer = $this->getLayout()->getBlock('fieldset_element_renderer');
        if ($renderer instanceof Varien_Data_Form_Element_Renderer_Interface) {
            Varien_Data_Form::setFieldsetElementRenderer($renderer);
        }
        return $result;
    }

    protected function _prepareForm() {
        parent::_prepareForm();

        $attribute  = $this->getAttributeObject();
        $form       = $this->getForm();
        $fieldset   = $form->getElement('base_fieldset');
        $helper     = Mage::helper('evogue_customer');

        $fieldset->removeField('frontend_class');
        $fieldset->removeField('is_unique');

        $element    = $form->getElement('frontend_input');
        $element->setValues($helper->getFrontendInputOptions());
        $element->setLabel(Mage::helper('evogue_customer')->__('Input Type'));
        $element->setRequired(true);

        $fieldset->addField('multiline_count', 'text', array(
            'name'      => 'multiline_count',
            'label'     => Mage::helper('evogue_customer')->__('Lines Count'),
            'title'     => Mage::helper('evogue_customer')->__('Lines Count'),
            'required'  => true,
            'class'     => 'validate-digits-range digits-range-2-20',
            'note'      => Mage::helper('evogue_customer')->__('Valid range 2-20')
        ), 'frontend_input');

        $fieldset->addField('input_validation', 'select', array(
            'name'      => 'input_validation',
            'label'     => Mage::helper('evogue_customer')->__('Input Validation'),
            'title'     => Mage::helper('evogue_customer')->__('Input Validation'),
            'values'    => array('' => Mage::helper('evogue_customer')->__('None'))
        ), 'default_value_textarea');

        $fieldset->addField('min_text_length', 'text', array(
            'name'      => 'min_text_length',
            'label'     => Mage::helper('evogue_customer')->__('Minimum Text Length'),
            'title'     => Mage::helper('evogue_customer')->__('Minimum Text Length'),
            'class'     => 'validate-digits',
        ), 'input_validation');

        $fieldset->addField('max_text_length', 'text', array(
            'name'      => 'max_text_length',
            'label'     => Mage::helper('evogue_customer')->__('Maximum Text Length'),
            'title'     => Mage::helper('evogue_customer')->__('Maximum Text Length'),
            'class'     => 'validate-digits',
        ), 'min_text_length');

        $fieldset->addField('max_file_size', 'text', array(
            'name'      => 'max_file_size',
            'label'     => Mage::helper('evogue_customer')->__('Maximum File Size (bytes)'),
            'title'     => Mage::helper('evogue_customer')->__('Maximum File Size (bytes)'),
            'class'     => 'validate-digits',
        ), 'max_text_length');

        $fieldset->addField('file_extensions', 'text', array(
            'name'      => 'file_extensions',
            'label'     => Mage::helper('evogue_customer')->__('File Extensions'),
            'title'     => Mage::helper('evogue_customer')->__('File Extensions'),
            'note'      => Mage::helper('evogue_customer')->__('Comma separated'),
        ), 'max_file_size');

        $fieldset->addField('max_image_width', 'text', array(
            'name'      => 'max_image_width',
            'label'     => Mage::helper('evogue_customer')->__('Maximum Image Width (px)'),
            'title'     => Mage::helper('evogue_customer')->__('Maximum Image Width (px)'),
            'class'     => 'validate-digits',
        ), 'file_extensions');

        $fieldset->addField('max_image_heght', 'text', array(
            'name'      => 'max_image_heght',
            'label'     => Mage::helper('evogue_customer')->__('Maximum Image Height (px)'),
            'title'     => Mage::helper('evogue_customer')->__('Maximum Image Height (px)'),
            'class'     => 'validate-digits',
        ), 'max_image_width');

        $fieldset->addField('input_filter', 'select', array(
            'name'      => 'input_filter',
            'label'     => Mage::helper('evogue_customer')->__('Input/Output Filter'),
            'title'     => Mage::helper('evogue_customer')->__('Input/Output Filter'),
            'values'    => array('' => Mage::helper('evogue_customer')->__('None')),
        ));

        $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

        $fieldset = $form->addFieldset('front_fieldset', array(
            'legend'    => Mage::helper('evogue_customer')->__('Frontend Properties')
        ));

        $fieldset->addField('is_visible', 'select', array(
            'name'      => 'is_visible',
            'label'     => Mage::helper('evogue_customer')->__('Show on Frontend'),
            'title'     => Mage::helper('evogue_customer')->__('Show on Frontend'),
            'values'    => $yesnoSource,
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name'      => 'sort_order',
            'label'     => Mage::helper('evogue_customer')->__('Sort Order'),
            'title'     => Mage::helper('evogue_customer')->__('Sort Order'),
            'required'  => true,
            'class'     => 'validate-digits'
        ));

        $fieldset->addField('used_in_forms', 'multiselect', array(
            'name'         => 'used_in_forms',
            'label'        => Mage::helper('evogue_customer')->__('Forms to Use In'),
            'title'        => Mage::helper('evogue_customer')->__('Forms to Use In'),
            'values'       => $helper->getCustomerAttributeFormOptions(),
            'value'        => $attribute->getUsedInForms(),
            'can_be_empty' => true,
        ))->setSize(5);

        if ($attribute->getId()) {
            $elements = array();
            if ($attribute->getIsSystem()) {
                $elements = array('sort_order', 'is_visible', 'is_required', 'used_in_forms');
            }
            if (!$attribute->getIsUserDefined() && !$attribute->getIsSystem()) {
                $elements = array('sort_order', 'used_in_forms');
            }
            foreach ($elements as $elementId) {
                $form->getElement($elementId)->setDisabled(true);
            }

            $inputTypeProp = $helper->getAttributeInputTypes($attribute->getFrontendInput());

            // input_filter
            if ($inputTypeProp['filter_types']) {
                $filterTypes = $helper->getAttributeFilterTypes();
                $values = $form->getElement('input_filter')->getValues();
                foreach ($inputTypeProp['filter_types'] as $filterTypeCode) {
                    $values[$filterTypeCode] = $filterTypes[$filterTypeCode];
                }
                $form->getElement('input_filter')->setValues($values);
            }

            // input_validation getAttributeValidateFilters
            if ($inputTypeProp['validate_filters']) {
                $filterTypes = $helper->getAttributeValidateFilters();
                $values = $form->getElement('input_validation')->getValues();
                foreach ($inputTypeProp['validate_filters'] as $filterTypeCode) {
                    $values[$filterTypeCode] = $filterTypes[$filterTypeCode];
                }
                $form->getElement('input_validation')->setValues($values);
            }
        }

        // apply scopes
        foreach ($helper->getAttributeElementScopes() as $elementId => $scope) {
            $element = $form->getElement($elementId);
            $element->setScope($scope);
            if ($this->getAttributeObject()->getWebsite()->getId()) {
                $element->setName('scope_' . $element->getName());
            }
        }

        $this->getForm()->setDataObject($this->getAttributeObject());

        Mage::dispatchEvent('evogue_customer_attribute_edit_tab_general_prepare_form', array(
            'form'      => $form,
            'attribute' => $attribute
        ));

        return $this;
    }

    protected function _initFormValues() {
        $attribute = $this->getAttributeObject();
        if ($attribute->getId() && $attribute->getValidateRules()) {
            $this->getForm()->addValues($attribute->getValidateRules());
        }
        $result = parent::_initFormValues();

        // get data using methods to apply scope
        $formValues = $this->getAttributeObject()->getData();
        foreach (array_keys($formValues) as $idx) {
            $formValues[$idx] = $this->getAttributeObject()->getDataUsingMethod($idx);
        }
        $this->getForm()->addValues($formValues);

        return $result;
    }

    public function getTabLabel() {
        return Mage::helper('evogue_customer')->__('Properties');
    }

    public function getTabTitle() {
        return Mage::helper('evogue_customer')->__('Properties');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
}
