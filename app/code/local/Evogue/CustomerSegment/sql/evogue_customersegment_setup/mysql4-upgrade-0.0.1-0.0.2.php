<?php
$installer = $this;
$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), "is_used_for_customer_segment", "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
$installer->getConnection()->addColumn($installer->getTable('customer/eav_attribute'), "is_used_for_customer_segment", "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
$installer->getConnection()->addColumn($installer->getTable('evogue_customersegment_segment'), "website_id", "smallint(5) unsigned NOT NULL DEFAULT 0");

$attributesOfEntities = array(
    'customer' => array('dob', 'email', 'firstname', 'group_id', 'lastname', 'gender', 'default_billing', 'default_shipping'),
    'customer_address' => array('firstname', 'lastname', 'company', 'street', 'city', 'region_id', 'postcode', 'country_id', 'telephone'),
    'order_address' => array('firstname', 'lastname', 'company', 'street', 'city', 'region_id', 'postcode', 'country_id', 'telephone', 'email'),
);
foreach ($attributesOfEntities as $entityTypeId => $attributes){
    foreach ($attributes as $attributeCode){
        $installer->updateAttribute($entityTypeId, $attributeCode, 'is_used_for_customer_segment', '1');
    }
}
