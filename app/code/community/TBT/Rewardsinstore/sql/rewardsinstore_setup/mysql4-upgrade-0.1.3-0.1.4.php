<?php

$installer = $this;
$installer->startSetup ();

$installer->addColumns($installer, $this->getTable('rewardsinstore/storefront' ), array ("
    `country_id` varchar(2) default NULL,
    `region_id` int(10) default NULL,
    `region` varchar(255) default NULL,
    `postcode` varchar(255) default NULL,
    `street` varchar(255) default NULL,
    `city` varchar(255) default NULL,
    `telephone` varchar(255) default NULL
    "));

$installer->endSetup();
