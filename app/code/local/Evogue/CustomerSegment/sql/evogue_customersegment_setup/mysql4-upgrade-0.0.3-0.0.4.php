<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('evogue_customersegment_event')}` (
    `segment_id` int(10) unsigned NOT NULL,
    `event` varchar(255) NOT NULL default '',
    KEY `IDX_EVOGUE_CUSTOMERSEGMENT_EVENT_EVENT` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('FK_EVOGUE_CUSTOMERSEGMENT_EVENT_SEGMENT',
    $installer->getTable('evogue_customersegment_event'), 'segment_id',
    $installer->getTable('evogue_customersegment_segment'), 'segment_id'
);

$installer->endSetup();
