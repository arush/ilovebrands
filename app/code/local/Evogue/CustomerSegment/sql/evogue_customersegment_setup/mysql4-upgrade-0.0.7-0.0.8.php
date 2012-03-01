<?php
$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('evogue_customersegment/segment'),
    'condition_sql',
    'mediumtext'
);
