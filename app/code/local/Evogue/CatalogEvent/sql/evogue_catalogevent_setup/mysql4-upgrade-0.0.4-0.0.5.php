<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()->dropColumn($installer->getTable('evogue_catalogevent/event'), 'status');

$installer->endSetup();
