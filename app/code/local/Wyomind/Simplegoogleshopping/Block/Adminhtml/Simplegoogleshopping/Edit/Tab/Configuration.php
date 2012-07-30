<?php
class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit_Tab_Configuration extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
       $form = new Varien_Data_Form();
       $model = Mage::getModel('simplegoogleshopping/simplegoogleshopping');
			
	  $model ->load($this->getRequest()->getParam('id'));
	   
	   $this->setForm($form);
	   $fieldset = $form->addFieldset('simplegoogleshopping_form', array('legend'=>$this->__('Configuration')));
		
		if ($model->getId()) {
			$fieldset->addField('simplegoogleshopping_id', 'hidden', array(
				'name' => 'simplegoogleshopping_id',
			));
		}
		$fieldset->addField('cron_expr', 'hidden', array(
			'name'  => 'cron_expr',
			'value' => $model->getCronExpr()
		));
                $fieldset->addField('simplegoogleshopping_category_filter', 'hidden', array(
			'name'  => 'simplegoogleshopping_category_filter',
			'value' => $model->getSimplegoogleshoppingCategoryFilter()
		));
		$fieldset->addField('simplegoogleshopping_categories', 'hidden', array(
			'name'  => 'simplegoogleshopping_categories',
			'value' => $model->getSimplegoogleshoppingCategories()
		));
		
		$fieldset->addField('simplegoogleshopping_visibility', 'hidden', array(
			'name'  => 'simplegoogleshopping_visibility',
			'value' => $model->getSimplegoogleshoppingVisibility()
		));
		
		$fieldset->addField('simplegoogleshopping_attributes', 'hidden', array(
			'name'  => 'simplegoogleshopping_attributes',
			'value' => $model->getSimplegoogleshoppingAttributes()
		));
		
		$fieldset->addField('simplegoogleshopping_type_ids', 'hidden', array(
			'name'  => 'simplegoogleshopping_type_ids',
			'value' => $model->getSimplegoogleshoppingTypeIds()
		));
		

		$fieldset->addField('simplegoogleshopping_filename', 'text', array(
			'label' => $this->__('Filename'),
			'name'  => 'simplegoogleshopping_filename',
			'class'=> 'refresh',
			'required' => true,
			'style' => 'width:400px',
			
			'value' => $model->getSimplegoogleshoppingFilename()
		));

		$fieldset->addField('simplegoogleshopping_path', 'text', array(
			'label' => $this->__('Path'),
			'name'  => 'simplegoogleshopping_path',
			'required' => true,
			'style' => 'width:400px',
		
			'value' => $model->getSimplegoogleshoppingPath()
		));
		
		
		$fieldset->addField('store_id', 'select', array(
				'label'    => $this->__('Store View'),
				'title'    => $this->__('Store View'),
				'name'     => 'store_id',
				'required' => true,
				'value'    => $model->getStoreId(),
				'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
			));
		
		$fieldset->addField('simplegoogleshopping_url', 'text', array(
			'label' => $this->__('Website url'),
			'name'  => 'simplegoogleshopping_url',
			'style' => 'width:400px',
			'class'=> 'refresh',
			'required' => true,
			'value' => $model->getSimplegoogleshoppingUrl()
		));
				
		$fieldset->addField('simplegoogleshopping_title', 'text', array(
			'label' => $this->__('Title'),
			'name'  => 'simplegoogleshopping_title',
			'style' => 'width:400px',
		'class'=> 'refresh',
			'required' => true,
			'value' => $model->getSimplegoogleshoppingXmlheaderdescription()
		));
		$fieldset->addField('simplegoogleshopping_description', 'textarea', array(
			'label' => $this->__('Description'),
			'name'  => 'simplegoogleshopping_description',
		'class'=> 'refresh',
			'required' => true,
			'style' => 'width:400px;height:100px',
			'value' => $model->getSimplegoogleshoppingXmlheaderdescription()
		));
		
		$fieldset->addField('simplegoogleshopping_xmlitempattern', 'textarea', array(
			'label' => $this->__('Xml template'),
			'name'  => 'simplegoogleshopping_xmlitempattern',
		'class'=> 'refresh',
			'required' => true,
			'style' => 'width:400px;height:350px ;letter-spacing:1px; width:400px;',
		 	'value' => $model->getSimplegoogleshoppingXmlitempattern(),
		));

		

		$fieldset->addField('generate', 'hidden', array(
			'name'     => 'generate',
			'value'    => ''
		)); 
		$fieldset->addField('continue', 'hidden', array(
			'name'     => 'continue',
			'value'    => ''
		)); 
		$fieldset->addField('copy', 'hidden', array(
			'name'     => 'copy',
			'value'    => ''
		)); 
		
		

		

  if ( Mage::registry('simplegoogleshopping_data') ) $form->setValues(Mage::registry('simplegoogleshopping_data')->getData());

  return parent::_prepareForm();
 }
}


 