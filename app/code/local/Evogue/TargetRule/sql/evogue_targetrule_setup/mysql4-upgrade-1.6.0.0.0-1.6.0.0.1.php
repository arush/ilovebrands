<?php

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('evogue_targetrule/rule'), 'action_select',
    'BLOB DEFAULT NULL');
$installer->getConnection()->addColumn($installer->getTable('evogue_targetrule/rule'), 'action_select_bind',
    'BLOB DEFAULT NULL');
$installer->endSetup();
