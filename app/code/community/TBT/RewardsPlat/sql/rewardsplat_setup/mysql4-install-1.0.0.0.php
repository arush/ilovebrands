<?php

$installer = $this;

$installer->startSetup();

$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_RewardsPlat/version' );

$msg_title = "Sweet Tooth Platinum ". $install_version ." was sucessfully installed!";
$msg_desc = "Sweet Tooth Platinum ". $install_version ." was just installed.";
Mage::helper ( 'rewards/mysql4_install' )->createInstallNotice ( $msg_title, $msg_desc );

$installer->endSetup();



