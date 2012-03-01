<?php

$installer = $this;
$installer->startSetup ();

// Create quote table
$installer->attemptQuery($installer, "
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/quote')}` (
        `quote_id` int(10) unsigned NOT NULL,
        `storefront_id` smallint(11) unsigned NOT NULL,
        PRIMARY KEY (`quote_id`),
        KEY `IX_STOREFRONT_ID` (`storefront_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Instore quotes';
");
    
// Add foreign key constraint to quote table
$installer->attemptQuery($installer, "
    ALTER TABLE `{$this->getTable('rewardsinstore/quote')}`
        ADD CONSTRAINT `FK_rewardsinstore_quote_sales_flat_quote`
            FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales/quote')}` (`entity_id`),
        ADD CONSTRAINT `FK_rewardsinstore_quote_rewardsinstore_storefront`
            FOREIGN KEY (`storefront_id`) REFERENCES `{$this->getTable('rewardsinstore/storefront')}` (`storefront_id`);
");

$installer->postDefaultInstallNotice();

$installer->endSetup();
