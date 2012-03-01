<?php

$installer = $this;
$installer->startSetup();

// Create storefront table
$installer->attemptQuery("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/storefront')}` (
        `storefront_id` smallint(11) unsigned NOT NULL auto_increment,
        `code` varchar(32) NOT NULL default '',
        `website_id` smallint(5) unsigned default '0',
        `name` varchar(255) NOT NULL default '',
        `description` varchar(255) NOT NULL default '',
        `sort_order` smallint(5) unsigned NOT NULL default '0',
        `country_id` varchar(2) default NULL,
        `region_id` int(10) default NULL,
        `region` varchar(255) default NULL,
        `postcode` varchar(255) default NULL,
        `street` varchar(255) default NULL,
        `city` varchar(255) default NULL,
        `telephone` varchar(255) default NULL,
        `is_active` tinyint(1) unsigned NOT NULL default '0',
        PRIMARY KEY  (`storefront_id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Storefronts';
");

// Create cartrule_attribute table
$installer->attemptQuery ("
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
$installer->attemptQuery("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/cartrule')}` (
        `rule_id` int(10) unsigned NOT NULL,
        `storefront_ids` text,
        PRIMARY KEY (`rule_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Shopping cart rules';
");

$installer->getConnection()
    ->addConstraint(
        'FK_REWARDSINSTORE_CARTRULE_ID',
        $installer->getTable('rewardsinstore/cartrule'), 
        'rule_id',
        $installer->getTable('salesrule'), 
        'rule_id',
        'cascade', 
        'cascade'
);

// Create quote table
$installer->attemptQuery("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/quote')}` (
        `quote_id` int(10) unsigned NOT NULL,
        `storefront_id` smallint(11) unsigned NOT NULL,
        PRIMARY KEY (`quote_id`),
        KEY `IX_STOREFRONT_ID` (`storefront_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Instore quotes';
");

$installer->getConnection()
    ->addConstraint(
        'FK_REWARDSINSTORE_QUOTE_ID',
        $installer->getTable('rewardsinstore/quote'), 
        'quote_id',
        $installer->getTable('sales/quote'), 
        'entity_id',
        'cascade', 
        'cascade'
);

$installer->getConnection()
    ->addConstraint(
        'FK_REWARDSINSTORE_QUOTE_STOREFRONT',
        $installer->getTable('rewardsinstore/quote'), 
        'storefront_id',
        $installer->getTable('rewardsinstore/storefront'), 
        'storefront_id',
        'cascade', 
        'cascade'
);

// Add our custom EAV attributes to the customer entity
$installer->addAttribute('customer', 'is_created_instore', array(
    'label'         => 'Created From Sweet Tooth Instore',
    'visible'       => false,
    'required'      => false,
    'type'          => 'int',
    'input'         => 'select',
    'source'        => 'eav/entity_attribute_source_boolean'
));

$installer->addAttribute('customer', 'storefront_id', array(
    'label'             => 'Storefront ID',
    'visible'           => false,
    'required'          => false,
    'type'              => 'int',
    'input'             => 'select',
    'source'            => '',
    'frontend_input'    => 'text',
    'source_model'      => ''
));

$installer->addAttribute('customer', 'is_welcome_email_sent', array(
    'label'         => '',
    'visible'       => false,
    'required'      => false,
    'type'          => 'int',
    'input'         => 'select',
    'source'        => 'eav/entity_attribute_source_boolean'
));

$installer->postDefaultInstallNotice();
$installer->endSetup();

?>
