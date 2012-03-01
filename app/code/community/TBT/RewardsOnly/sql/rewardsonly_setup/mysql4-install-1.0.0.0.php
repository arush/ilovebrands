<?php

$installer = $this;

$installer->startSetup();


Mage::helper('rewards/mysql4_install')->addColumns($installer, $this->getTable('catalogrule'), array (
        "`points_only_mode` TINYINT(1) DEFAULT '0'",
));


/*$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_RewardsOnly/version' );

$msg_title = "Sweet Tooth Points-Only ". $install_version ." was sucessfully installed!";
$msg_desc = "Sweet Tooth Points-Only ". $install_version ." was just installed.";
Mage::helper ( 'rewards/mysql4_install' )->createInstallNotice ( $msg_title, $msg_desc );*/

$installer->endSetup();



