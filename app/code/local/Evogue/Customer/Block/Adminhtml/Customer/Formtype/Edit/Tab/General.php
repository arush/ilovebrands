<?php
class Evogue_Customer_Block_Adminhtml_Customer_Formtype_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        $this->setDestElementId('edit_form');
        $this->setShowGlobalIcon(false);
        parent::__construct();
    }

    protected function _prepareForm() {
    
        $model      = Mage::registry('current_form_type');

        $form       = new Varien_Data_Form();
        $fieldset   = $form->addFieldset('general_fieldset', array(
            'legend'    => Mage::helper('evogue_customer')->__('General Information')
        ));

        $fieldset->addField('continue_edit', 'hidden', array(
            'name'      => 'continue_edit',
            'value'     => 0
        ));
        $fieldset->addField('type_id', 'hidden', array(
            'name'      => 'type_id',
            'value'     => $model->getId()
        ));

        $fieldset->addField('form_type_data', 'hidden', array(
            'name'      => 'form_type_data'
        ));

        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => Mage::helper('evogue_customer')->__('Form Code'),
            'title'     => Mage::helper('evogue_customer')->__('Form Code'),
            'required'  => true,
            'class'     => 'validate-code',
            'disabled'  => true,
            'value'     => $model->getCode()
        ));

        $fieldset->addField('label', 'text', array(
            'name'      => 'label',
            'label'     => Mage::helper('evogue_customer')->__('Form Title'),
            'title'     => Mage::helper('evogue_customer')->__('Form Title'),
            'required'  => true,
            'value'     => $model->getLabel()
        ));

        $options = Mage::getModel('core/design_source_design')->getAllOptions(false, true);
        array_unshift($options, array(
            'label' => Mage::helper('evogue_customer')->__('All Themes'),
            'value' => ''
        ));
        $fieldset->addField('theme', 'select', array(
            'name'      => 'theme',
            'label'     => Mage::helper('evogue_customer')->__('For Theme'),
            'title'     => Mage::helper('evogue_customer')->__('For Theme'),
            'values'    => $options,
            'value'     => $model->getTheme(),
            'disabled'  => true
        ));

        $fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('evogue_customer')->__('Store View'),
            'title'     => Mage::helper('evogue_customer')->__('Store View'),
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            'value'     => $model->getStoreId(),
            'disabled'  => true
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel() {
        return Mage::helper('evogue_customer')->__('General');
    }

    public function getTabTitle() {
        return Mage::helper('evogue_customer')->__('General');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
}
