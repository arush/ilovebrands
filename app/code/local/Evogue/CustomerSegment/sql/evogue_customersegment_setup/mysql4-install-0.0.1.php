<?php


$installer = $this;

$installer->run("CREATE TABLE `{$this->getTable('evogue_customersegment_segment')}` (
        `segment_id` int(10) unsigned NOT NULL auto_increment,
        `name` varchar(255) NOT NULL default '',
        `description` text NOT NULL,
        `is_active` tinyint(1) NOT NULL default '0',
        `conditions_serialized` mediumtext NOT NULL,
        `processing_frequency` int(100) NOT NULL,
    PRIMARY KEY  (`segment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
