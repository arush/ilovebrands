<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$connection = $installer->getConnection();
$tableBalance = $installer->getTable('evogue_customerbalance/balance');
$connection->addColumn($tableBalance, 'base_currency_code', 'CHAR( 3 ) NULL DEFAULT NULL');
$connection->changeColumn($tableBalance, 'website_id', 'website_id', 'SMALLINT(5) UNSIGNED NULL DEFAULT NULL');
$connection->addConstraint('FK_CUSTOMERBALANCE_WEBSITE', $tableBalance, 'website_id', $installer->getTable('core/website'), 'website_id', 'SET NULL');

$installer->endSetup();
