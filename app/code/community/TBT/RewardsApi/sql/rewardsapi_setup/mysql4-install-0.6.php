<?php

$installer = $this;

$installer->startSetup();
/*
$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_RewardsApi/version' );

$msg_title = "Sweet Tooth API ". $install_version ." was sucessfully installed!";
$msg_desc = "Sweet Tooth API ". $install_version ." was just installed.";
Mage::helper ( 'rewards/mysql4_install' )->createInstallNotice ( $msg_title, $msg_desc );*/

$installer->endSetup();



