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
 * @since        Class available since Release 1.3
 */

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('gomage_feed_entity')}` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `store_id` smallint(6) NOT NULL,
  `type` varchar(32) NOT NULL,
  `status` smallint(1) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `filter` text,
  `generated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `show_headers` tinyint(1) default NULL,
  `enclosure` varchar(32) default NULL,
  `delimiter` varchar(32) default NULL,
  `remove_lb` tinyint(1) DEFAULT '0',
  `ftp_active` int(1) default '0',
  `ftp_host` varchar(128) default '',
  `ftp_user_name` varchar(256) default '',
  `ftp_user_pass` varchar(256) default '',
  `ftp_dir` varchar(256) default '/',
  `ftp_passive_mode` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='GoMage Catalog Feed' AUTO_INCREMENT=1;
");

$installer->endSetup(); 