<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
CREATE TABLE {$installer->getTable('evogue_customerbalance_history')} (
`primary_id` INT( 10 ) NOT NULL AUTO_INCREMENT,
`customer_id` INT( 10 ) UNSIGNED NOT NULL,
`website_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
`action` TINYINT( 1 ) UNSIGNED NOT NULL,
`date` DATETIME NOT NULL,
`admin_user_id` MEDIUMINT( 9 ) UNSIGNED NOT NULL,
`delta` DECIMAL( 12, 4 ) NOT NULL,
`order_increment_id` VARCHAR(30) NULL,
    PRIMARY KEY ( `primary_id` ),
    KEY `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` (`customer_id`),
    KEY `FK_CUSTOMERBALANCE_HISTORY_CORE_WEBSITE` ( `website_id` ),
    CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CUSTOMER_ENTITY` FOREIGN KEY (customer_id) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_CUSTOMERBALANCE_HISTORY_CORE_WEBSITE` FOREIGN KEY ( website_id ) REFERENCES {$installer->getTable('core_website')}( `website_id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
");

$installer->endSetup();
