<?php
$installer = $this;
$customerTable = $installer->getTable('evogue_customersegment/customer');
$segmentTable  = $installer->getTable('evogue_customersegment/segment');
$websiteTable  = $installer->getTable('evogue_customersegment/website');
$adapter = $installer->getConnection();

$installer->run("CREATE TABLE `{$websiteTable}` (
  `segment_id` int(10) unsigned NOT NULL,
  `website_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`segment_id`,`website_id`),
  KEY `FK_EE_SEGMENT_WEBSITE` (`website_id`),
  CONSTRAINT `FK_EE_SEGMENT_SEFMENT` FOREIGN KEY (`segment_id`)
    REFERENCES `{$this->getTable('evogue_customersegment/segment')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EE_SEGMENT_WEBSITE` FOREIGN KEY (`website_id`)
    REFERENCES `{$this->getTable('core/website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Segment to website association';
");

$adapter->addColumn($customerTable, 'website_id', 'smallint(5) unsigned NOT NULL');

$installer->run("
UPDATE
    `{$customerTable}` AS sc,
    `{$segmentTable}` AS s
SET sc.website_id=s.website_id
WHERE sc.segment_id=s.segment_id;

INSERT INTO `{$websiteTable}` SELECT segment_id, website_id FROM `{$segmentTable}`;
");

$adapter->dropColumn($segmentTable, 'website_id');
$adapter->addConstraint(
    'FK_EE_CUSTOMER_SEGMENT_WEBSIE',
    $customerTable,
    'website_id',
    $installer->getTable('core/website'),
    'website_id'
);

$adapter->addKey($customerTable, 'FK_CUSTOMER', array('customer_id'));
$adapter->dropKey($customerTable, 'UNQ_EVOGUE_CUSTOMERSEGMENT_CUSTOMER');
$adapter->addKey($customerTable, 'UNQ_CUSTOMER', array('segment_id', 'website_id', 'customer_id'), 'unique');
