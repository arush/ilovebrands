<?php
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$connection->addColumn($this->getTable('evogue_customersegment_customer'), 'added_date', 'DATETIME NOT NULL');
$connection->addColumn($this->getTable('evogue_customersegment_customer'), 'updated_date', 'DATETIME NOT NULL');

$connection->addKey($this->getTable('evogue_customersegment_customer'), 'UNQ_ENVOGUE_CUSTOMERSEGMENT_CUSTOMER', array('customer_id', 'segment_id'), 'unique');

$installer->endSetup();
