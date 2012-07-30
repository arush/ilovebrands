<?php
class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit_Tab_Cron extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
       $form = new Varien_Data_Form();
        $model = Mage::getModel('simplegoogleshopping/simplegoogleshopping');

        $model->load($this->getRequest()->getParam('id'));

        $this->setForm($form);
        $fieldset = $form->addFieldset('simplegoogleshopping_form', array('legend' => $this->__('Cron')));


        $this->setTemplate('simplegoogleshopping/cron.phtml');


        if (Mage::registry('simplegoogleshopping_data'))
            $form->setValues(Mage::registry('simplegoogleshopping_data')->getData());

        return parent::_prepareForm();
 }
}


 