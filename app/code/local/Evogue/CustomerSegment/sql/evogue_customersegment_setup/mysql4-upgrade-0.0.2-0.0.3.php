<?php
$installer = $this;
$installer->run("UPDATE {$installer->getTable('catalog/eav_attribute')}
    SET is_used_for_customer_segment = is_used_for_price_rules");
