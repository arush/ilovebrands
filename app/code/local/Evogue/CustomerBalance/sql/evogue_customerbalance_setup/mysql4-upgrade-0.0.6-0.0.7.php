<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->addAttribute('quote', 'use_customer_balance', array('type'=>'int'));

$installer->endSetup();
