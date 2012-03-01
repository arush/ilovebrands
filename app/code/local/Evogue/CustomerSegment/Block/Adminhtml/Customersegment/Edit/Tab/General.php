<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('current_customer_segment');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('segment_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('evogue_customersegment')->__('General Properties')));

        if ($model->getId()) {
            $fieldset->addField('segment_id', 'hidden', array(
                'name' => 'segment_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('evogue_customersegment')->__('Segment Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('evogue_customersegment')->__('Description'),
            'style' => 'width: 98%; height: 100px;',
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', array(
                'name'      => 'website_ids[]',
                'value'     => $websiteId
            ));
            $model->setWebsiteIds($websiteId);
        } else {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'      => 'website_ids',
                'label'     => Mage::helper('evogue_customersegment')->__('Assigned to Website'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
            ));
        }

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('evogue_customersegment')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('evogue_customersegment')->__('Active'),
                '0' => Mage::helper('evogue_customersegment')->__('Inactive'),
            ),
        ));
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        } else {
            $model->getWebsiteIds();
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
