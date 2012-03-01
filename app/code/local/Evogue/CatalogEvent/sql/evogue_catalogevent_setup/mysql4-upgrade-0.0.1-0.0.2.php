<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('evogue_catalogevent/event_image')}`;
CREATE TABLE `{$installer->getTable('evogue_catalogevent/event_image')}` (
    `event_id` int(10) unsigned NOT NULL,
    `store_id` smallint (5) unsigned NOT NULL,
    `image` varchar(255) NOT NULL,
    UNIQUE KEY `scope` (`event_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Catalog Event Images';
");

$installer->getConnection()->addConstraint(
    'EVOGUE_CATALOGEVENT_EVENT_IMAGE_EVENT',
    $installer->getTable('evogue_catalogevent/event_image'),
    'event_id',
    $installer->getTable('evogue_catalogevent/event'),
    'event_id'
);

$installer->getConnection()->addConstraint(
    'EVOGUE_CATALOGEVENT_EVENT_IMAGE_STORE',
    $installer->getTable('evogue_catalogevent/event_image'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id'
);

$installer->getConnection()->addColumn(
    $installer->getTable('evogue_catalogevent/event'),
    'sort_order',
    ' int(10) unsigned DEFAULT NULL'
);

$now = Mage::app()->getLocale()->date()
    ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

$installer->getConnection()->insert($installer->getTable('cms/block'), array(
    'title' => 'Catalog Events Lister',
    'identifier' => 'catalog_events_lister',
    'content' => '{{block type="evogue_catalogevent/event_lister" name="catalog.event.lister" template="evogue/catalogevent/lister.phtml"}}',
    'creation_time' => $now,
    'update_time' => $now,
    'is_active' => 1
));

$blockId = $installer->getConnection()->lastInsertId();
$select = $installer->getConnection()->select()
    ->from($installer->getTable('core/store'), array('block_id' => new Zend_Db_Expr($blockId), 'store_id'))
    ->where('store_id > 0');

$installer->run($select->insertFromSelect($installer->getTable('cms/block_store')), array('block_id', 'store_id'));

$installer->endSetup();
