<?php
$installer = $this;
//$installer->installEntities();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'customerurl', array(
	'label'				=> 'Customer Url',
	'type'				=> 'varchar',
	'input'				=> 'text',
	'visible'			=> true,
	'required'			=> true,
	'unique'			=> true,
	'is_user_defined'	=> true,
	'position'			=> 1)
);

$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', 'customerurl');
$attribute->setData('used_in_forms',   array('customer_account_edit','customer_account_create','adminhtml_customer'));
$attribute->save();
