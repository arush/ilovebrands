<?php

$installer = $this;

$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('oneall_identifiers')};
CREATE TABLE IF NOT EXISTS `{$installer->getTable('oneall_identifiers')}` (
  `oneall_identifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `identity_token` varchar(255) NOT NULL,
  `customer_id` int(11) unsigned NOT NULL,
  `profile_name` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL,
  PRIMARY KEY (`oneall_identifier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");


//required for Magento 1.5.x and up
//$eavConfig = Mage::getSingleton('eav/config');
//$attribute = $eavConfig->getAttribute('customer', 'onealluuid');
//$attribute->setData('used_in_forms',   array(/*'customer_account_edit','customer_account_create',*/'adminhtml_customer'));
//$attribute->save();


$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'oneall_user_token', array(
	'label'				=> 'Oneall User Token',
	'type'				=> 'varchar',
	'input'				=> 'text',
	'visible'			=> false,
	'required'			=> false,
	'unique'			=> true,
	'is_user_defined'	=> false,
	'position'			=> 100)
);

$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', 'oneall_user_token');
$attribute->setData('used_in_forms',   array('customer_account_edit','customer_account_create','adminhtml_customer'));
$attribute->save();

$installer->endSetup();




// $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
