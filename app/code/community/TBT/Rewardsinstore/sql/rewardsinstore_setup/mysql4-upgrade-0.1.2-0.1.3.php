<?php

$installer = $this;
$installer->startSetup ();

// Add our custom EAV attributes to the customer entity
$installer->addAttribute('customer', 'is_created_instore', array(
    'label'        => 'Created From Sweet Tooth Instore',
    'visible'      => false,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'        => 'eav/entity_attribute_source_boolean'
));
$installer->addAttribute('customer', 'storefront_id', array(
    'label'        => 'Storefront ID',
    'visible'      => false,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'       => 'rewardsinstore/customer_attribute_source_storefront'
));
$installer->addAttribute('customer', 'is_welcome_email_sent', array(
    'label'        => '',
    'visible'      => false,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'        => 'eav/entity_attribute_source_boolean'
));

$installer->endSetup();
