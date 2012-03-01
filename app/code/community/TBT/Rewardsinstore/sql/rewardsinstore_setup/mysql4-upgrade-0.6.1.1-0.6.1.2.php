<?php

$installer = $this;
$installer->startSetup ();

$installer->updateAttribute('customer', 'storefront_id', 'frontend_input', 'text');
$installer->updateAttribute('customer', 'storefront_id', 'source_model', '');

$installer->postDefaultInstallNotice();

$installer->endSetup();
