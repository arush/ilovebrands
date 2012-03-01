<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer,
		"CREATE TABLE IF NOT EXISTS `{$this->getTable ('rewards/catalogrule_product' )}` (
			`rewards_catalogrule_product_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`rule_date` DATE NOT NULL DEFAULT '0000-00-00',
			`customer_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
			`product_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
			`rules_hash` TEXT NULL,
			`website_id` SMALLINT(5) UNSIGNED NOT NULL,
			`latest_start_date` DATE NULL DEFAULT NULL,
			`earliest_end_date` DATE NULL DEFAULT NULL,
			PRIMARY KEY (`rewards_catalogrule_product_id`),
			UNIQUE INDEX `rule_product_instance` (`rule_date`, `website_id`, `customer_group_id`, `product_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, "FK_REWARDS_CATALOGRULE_PRODUCT_CUSTOMER_GROUP", $this->getTable( 'rewards/catalogrule_product' ), 
	"customer_group_id", $this->getTable( 'customer_group' ), "customer_group_id", "CASCADE", "CASCADE" );

Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, "FK_REWARDS_CATALOGRULE_PRODUCT_PRODUCT", $this->getTable( 'rewards/catalogrule_product' ), 
	"product_id", $this->getTable( 'catalog_product_entity' ), "entity_id", "CASCADE", "CASCADE" );

Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, "FK_REWARDS_CATALOGRULE_PRODUCT_WEBSITE", $this->getTable( 'rewards/catalogrule_product' ), 
	"website_id", $this->getTable( 'core_website' ), "website_id", "CASCADE", "CASCADE" );


$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_Rewards/version' );
$msg_title = "Sweet Tooth v{$install_version} was successfully installed!";
$msg_desc = "Sweet Tooth v{$install_version} was successfully installed on your store.";
Mage::helper( 'rewards/mysql4_install' )->createInstallNotice( $msg_title, $msg_desc );


// Clear cache.
Mage::helper( 'rewards/mysql4_install' )->prepareForDb();

$installer->endSetup(); 
