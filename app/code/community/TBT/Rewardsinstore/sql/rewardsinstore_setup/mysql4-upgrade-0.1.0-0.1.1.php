<?php

$installer = $this;
$installer->startSetup ();

// Create order table
$installer->attemptQuery($installer, "
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsinstore/order')}` (
        `order_id` int(10) unsigned NOT NULL,
        `storefront_id` smallint(11) unsigned NOT NULL,
        PRIMARY KEY (`order_id`),
        KEY `FK_rewardsinstore_order_rewardsinstore_storefront` (`storefront_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Instore orders';
");
    
// Add foreign key constraint to order table
$installer->attemptQuery($installer, "
    ALTER TABLE `{$this->getTable('rewardsinstore/order')}`
        ADD CONSTRAINT `FK_rewardsinstore_order_sales_flat_order`
            FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales/order')}` (`entity_id`),
        ADD CONSTRAINT `FK_rewardsinstore_order_rewardsinstore_storefront`
            FOREIGN KEY (`storefront_id`) REFERENCES `{$this->getTable('rewardsinstore/storefront')}` (`storefront_id`);
");

$installer->endSetup();
