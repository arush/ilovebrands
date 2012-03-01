<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->addAttribute('creditmemo', 'base_customer_balance_total_refunded', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'customer_balance_total_refunded', array('type'=>'decimal'));

$installer->addAttribute('order', 'base_customer_balance_total_refunded', array('type'=>'decimal'));
$installer->addAttribute('order', 'customer_balance_total_refunded', array('type'=>'decimal'));

$installer->endSetup();
