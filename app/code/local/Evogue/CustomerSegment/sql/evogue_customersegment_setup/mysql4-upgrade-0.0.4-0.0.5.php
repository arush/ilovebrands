<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('evogue_customersegment_customer')}` (
    `segment_id` int(10) unsigned NOT NULL,
    `customer_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('FK_EVOGUE_CUSTOMERSEGMENT_CUSTOMER_SEGMENT',
    $installer->getTable('evogue_customersegment_customer'), 'segment_id',
    $installer->getTable('evogue_customersegment_segment'), 'segment_id'
);

$installer->getConnection()->addConstraint('FK_EVOGUE_CUSTOMERSEGMENT_CUSTOMER_CUSTOMER',
    $installer->getTable('evogue_customersegment_customer'), 'customer_id',
    $installer->getTable('customer_entity'), 'entity_id'
);

$installer->endSetup();
