<?php
$installer = $this;

$installer->startSetup();

$installer->updateAttribute('catalog_product',  'related_targetrule_position_limit', 'backend_model', 'evogue_targetrule/catalog_product_attribute_backend_rule');
$installer->updateAttribute('catalog_product',  'related_targetrule_position_behavior', 'backend_model', 'evogue_targetrule/catalog_product_attribute_backend_rule');
$installer->updateAttribute('catalog_product',  'upsell_targetrule_position_limit', 'backend_model', 'evogue_targetrule/catalog_product_attribute_backend_rule');
$installer->updateAttribute('catalog_product',  'upsell_targetrule_position_behavior', 'backend_model', 'evogue_targetrule/catalog_product_attribute_backend_rule');

$installer->endSetup();
