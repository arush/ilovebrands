<?php
class Evogue_Customer_Block_Adminhtml_Customer_Formtype_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _getFormType() {
        return Mage::registry('current_form_type');
    }

    protected function _prepareForm() {
        $editMode = Mage::registry('edit_mode');
        if ($editMode == 'edit') {
            $saveUrl = $this->getUrl('*/*/save');
            $showNew = false;
        } else {
            $saveUrl = $this->getUrl('*/*/create');
            $showNew = true;
        }
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $saveUrl,
            'method'    => 'post'
        ));

        if ($showNew) {
            $fieldset = $form->addFieldset('base_fieldset', array(
                'legend' => Mage::helper('evogue_customer')->__('General Information'),
                'class'  => 'fieldset-wide'
            ));

            $options = $this->_getFormType()->getCollection()->toOptionArray();
            array_unshift($options, array(
                'label' => Mage::helper('evogue_customer')->__('-- Please Select --'),
                'value' => ''
            ));
            $fieldset->addField('type_id', 'select', array(
                'name'      => 'type_id',
                'label'     => Mage::helper('evogue_customer')->__('Based On'),
                'title'     => Mage::helper('evogue_customer')->__('Based On'),
                'required'  => true,
                'values'    => $options
            ));

            $fieldset->addField('label', 'text', array(
                'name'      => 'label',
                'label'     => Mage::helper('evogue_customer')->__('Form Label'),
                'title'     => Mage::helper('evogue_customer')->__('Form Label'),
                'required'  => true,
            ));

            $options = Mage::getModel('core/design_source_design')->getAllOptions(false);
            array_unshift($options, array(
                'label' => Mage::helper('evogue_customer')->__('All Themes'),
                'value' => ''
            ));
            $fieldset->addField('theme', 'select', array(
                'name'      => 'theme',
                'label'     => Mage::helper('evogue_customer')->__('For Theme'),
                'title'     => Mage::helper('evogue_customer')->__('For Theme'),
                'values'    => $options
            ));

            $fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => Mage::helper('evogue_customer')->__('Store View'),
                'title'     => Mage::helper('evogue_customer')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));

            $form->setValues($this->_getFormType()->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
