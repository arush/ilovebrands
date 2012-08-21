<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 2.0
 */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('gomage_feed_entity')}`
  ADD COLUMN `use_disabled` tinyint(1) NOT NULL default '1';
");

$installer->run("
ALTER TABLE `{$this->getTable('gomage_feed_entity')}`
  ADD COLUMN `upload_hour_to` smallint(6) default NULL;
");

$installer->run("
ALTER TABLE `{$this->getTable('gomage_feed_entity')}`
  ADD COLUMN `restart_cron` smallint(6) default NULL;
");

$installer->endSetup();