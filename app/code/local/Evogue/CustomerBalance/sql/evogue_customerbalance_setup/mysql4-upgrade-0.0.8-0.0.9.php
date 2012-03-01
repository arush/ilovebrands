<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableBalance = $installer->getTable('evogue_customerbalance/balance');
$installer->run("
    DELETE FROM {$tableBalance} WHERE website_id IS NULL;
");

$installer->getConnection()->dropForeignKey($tableBalance, 'FK_CUSTOMERBALANCE_WEBSITE');
$installer->getConnection()->dropKey($tableBalance, 'FK_CUSTOMERBALANCE_WEBSITE');

$installer->getConnection()->changeColumn($tableBalance, 'website_id', 'website_id', 'smallint(5) unsigned NOT NULL DEFAULT 0');

$installer->getConnection()->addConstraint('FK_CUSTOMERBALANCE_WEBSITE', $tableBalance, 'website_id', $installer->getTable('core/website'), 'website_id');

$installer->endSetup();
