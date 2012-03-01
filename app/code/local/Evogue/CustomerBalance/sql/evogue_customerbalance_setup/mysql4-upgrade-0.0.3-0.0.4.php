<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("DROP TABLE {$installer->getTable('evogue_customerbalance_history')};");
$installer->run("
CREATE TABLE {$installer->getTable('evogue_customerbalance_history')} (
 `primary_id` int(10) NOT NULL auto_increment,
 `customer_id` int(10) unsigned NOT NULL,
 `website_id` smallint(5) unsigned NOT NULL,
 `action` tinyint(1) unsigned NOT NULL,
 `date` datetime NOT NULL,
 `admin_user` varchar(255) NOT NULL,
 `delta` decimal(12,4) NOT NULL,
 `balance` decimal(12,4) NOT NULL,
 `order_increment_id` varchar(30) default NULL,
 `notified` tinyint(1) unsigned NOT NULL default '0',
 PRIMARY KEY  (`primary_id`),
 KEY `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` (`customer_id`),
 CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$installer->endSetup();
