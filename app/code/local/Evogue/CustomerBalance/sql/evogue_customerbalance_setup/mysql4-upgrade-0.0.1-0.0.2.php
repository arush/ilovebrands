<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("ALTER TABLE {$installer->getTable('evogue_customerbalance')} CHANGE `website_id` `website_id` SMALLINT( 5 ) UNSIGNED NOT NULL;");

$installer->run("
ALTER TABLE {$installer->getTable('evogue_customerbalance')} ADD KEY `FK_CUSTOMERBALANCE_CORE_WEBSITE` ( `website_id` ) ,
ADD CONSTRAINT `FK_CUSTOMERBALANCE_CORE_WEBSITE` FOREIGN KEY ( website_id ) REFERENCES {$installer->getTable('core_website')}( `website_id` ) ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();
