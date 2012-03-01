<?php

$installer = $this;
$installer->startSetup();

// Create storefront table
$installer->attemptQuery($installer, "
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/storefront')}` (
        `storefront_id` smallint(11) unsigned NOT NULL auto_increment,
        `code` varchar(32) NOT NULL default '',
        `website_id` smallint(5) unsigned default '0',
        `name` varchar(255) NOT NULL default '',
        `description` varchar(255) NOT NULL default '',
        `sort_order` smallint(5) unsigned NOT NULL default '0',
        `is_active` tinyint(1) unsigned NOT NULL default '0',
        PRIMARY KEY  (`storefront_id`),
        UNIQUE KEY `code` (`code`),
        KEY `FK_STORE_WEBSITE` (`website_id`),
        KEY `is_active` (`is_active`,`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Storefronts';
");

// Create cartrule_attribute table
$installer->attemptQuery ( $installer, "
    CREATE TABLE IF NOT EXISTS {$this->getTable('rewardsinstore/cartrule_attribute')} (
        `cartrule_attribute_id` smallint(11) unsigned NOT NULL auto_increment,
        `code` varchar(32) NOT NULL default '',
        `frontend_label` varchar(255) NOT NULL default '',
        PRIMARY KEY  (`cartrule_attribute_id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Instore Cart Attributes';
");

// Insert all Mage cart attributes
$installer->insertCartruleAttributes();

// Create cartrule table
$installer->attemptQuery($installer, "
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/cartrule')}` (
        `rule_id` int(10) unsigned NOT NULL,
        `storefront_ids` text,
        PRIMARY KEY (`rule_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Shopping cart rules';
");

// Add foreign key constraint to cartrule table
$installer->attemptQuery($installer, "
    ALTER TABLE `{$this->getTable('rewardsinstore/cartrule')}`
        ADD CONSTRAINT `FK_rewardsinstore_cartrule_salesrule`
            FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('salesrule')}` (`rule_id`);
");

$installer->endSetup();

?>
