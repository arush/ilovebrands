<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('evogue_customer/sales_order')}`;
CREATE TABLE `{$installer->getTable('evogue_customer/sales_order')}` (
  `entity_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entity_id`),
  CONSTRAINT `FK_EVOGUE_CUSTOMER_SALES_ORDER` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('sales/order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('evogue_customer/sales_order_address')}`;
CREATE TABLE `{$installer->getTable('evogue_customer/sales_order_address')}` (
  `entity_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entity_id`),
  CONSTRAINT `FK_EVOGUE_CUSTOMER_SALES_ORDER_ADDRESS` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('sales/order_address')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('evogue_customer/sales_quote')}`;
CREATE TABLE `{$installer->getTable('evogue_customer/sales_quote')}` (
  `entity_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entity_id`),
  CONSTRAINT `FK_EVOGUE_CUSTOMER_SALES_QUOTE` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('sales/quote')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('evogue_customer/sales_quote_address')}`;
CREATE TABLE `{$installer->getTable('evogue_customer/sales_quote_address')}` (
  `entity_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entity_id`),
  CONSTRAINT `FK_EVOGUE_CUSTOMER_SALES_QUOTE_ADDRESS` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('sales/quote_address')}` (`address_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
