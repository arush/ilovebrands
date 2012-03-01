<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('evogue_catalogevent/event')}`;
CREATE TABLE `{$installer->getTable('evogue_catalogevent/event')}` (
    `event_id` int(10) unsigned NOT NULL auto_increment,
    `category_id` int(10) unsigned default NULL,
    `date_start` datetime default NULL,
    `date_end` datetime default NULL,
    `status` enum('upcoming','open','closed') NOT NULL default 'upcoming',
    `display_state` tinyint(3) unsigned default 0,
    PRIMARY KEY  (`event_id`),
    UNIQUE KEY `category_id` (`category_id`),
    KEY `sort_order` (`date_start`,`date_end`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Events';
");

$fkName = strtoupper($installer->getTable('evogue_catalogevent/event'));

$installer->getConnection()->addConstraint($fkName . '_CATEGORY', $installer->getTable('evogue_catalogevent/event'), 'category_id', $this->getTable('catalog/category'), 'entity_id');

$installer->addAttribute('quote_item', 'event_id', array('type' => 'int'));
$installer->addAttribute('quote_item', 'event_name', array());
$installer->addAttribute('order_item', 'event_id', array('type'=>'int'));
$installer->addAttribute('order_item', 'event_name', array());


$installer->endSetup();
