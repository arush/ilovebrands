<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableBalance = $installer->getTable('evogue_customerbalance/balance');
$tableHistory = $installer->getTable('evogue_customerbalance/balance_history');
$installer->run("
DROP TABLE IF EXISTS `{$tableHistory}`;
DROP TABLE IF EXISTS `{$tableBalance}`;
CREATE TABLE `{$tableBalance}` (
  `balance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL DEFAULT 0,
  `website_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `amount` decimal(12,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`balance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `{$tableHistory}` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `balance_id` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NULL DEFAULT NULL,
  `action` tinyint(3) unsigned NOT NULL default '0',
  `balance_amount` decimal(12,4) unsigned NOT NULL DEFAULT 0,
  `balance_delta` decimal(12,4) NOT NULL DEFAULT 0,
  `additional_info` tinytext COLLATE utf8_general_ci NULL,
  `is_customer_notified` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->getConnection()->addConstraint('FK_CUSTOMERBALANCE_CUSTOMER', $tableBalance, 'customer_id',
    $installer->getTable('customer/entity'), 'entity_id'
);
$installer->getConnection()->addKey($tableBalance, 'UNQ_CUSTOMERBALANCE_CW', array('customer_id', 'website_id'), 'unique');
$installer->getConnection()->addConstraint('FK_CUSTOMERBALANCE_HISTORY_BALANCE', $tableHistory, 'balance_id',
    $tableBalance, 'balance_id'
);

$installer->endSetup();
