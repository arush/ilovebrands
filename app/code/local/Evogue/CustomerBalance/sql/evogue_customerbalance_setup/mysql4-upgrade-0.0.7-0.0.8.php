<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableBalance = $installer->getTable('evogue_customerbalance/balance');
$installer->getConnection()->changeColumn($tableBalance, 'website_id', 'website_id', 'smallint(5) unsigned NULL DEFAULT NULL');
$installer->getConnection()->addConstraint('FK_CUSTOMERBALANCE_WEBSITE', $tableBalance, 'website_id', $installer->getTable('core/website'), 'website_id', 'SET NULL');

$installer->endSetup();
