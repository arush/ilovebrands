<?php

$installer = $this;
$installer->getConnection()->dropColumn($installer->getTable('sales/quote_item'), 'event_name');
$installer->getConnection()->dropColumn($installer->getTable('sales/order_item'), 'event_name');
