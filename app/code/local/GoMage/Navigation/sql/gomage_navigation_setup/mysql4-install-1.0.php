<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Release available since Release 1.0
 */

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('gomage_navigation_attribute')}` (
  `attribute_id` smallint(5) unsigned NOT NULL,
  `filter_type` tinyint(2) NOT NULL,
  `image_align` tinyint(1) NOT NULL,
  `image_width` int(3) NOT NULL,
  `image_height` int(3) NOT NULL,
  `show_minimized` tinyint(1) NOT NULL,
  `show_image_name` tinyint(1) NOT NULL,
  `show_help` tinyint(1) NOT NULL,
  `show_checkbox` tinyint(1) NOT NULL,
  `popup_text` varchar(256) NOT NULL,
  `popup_width` int(3) NOT NULL,
  `popup_height` int(3) NOT NULL,
  PRIMARY KEY  (`attribute_id`),
  CONSTRAINT `FK_gomage_navigation_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{$this->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8

");
  
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('gomage_navigation_attribute_option')}` (
  `attribute_id` smallint(5) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  `filename` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `size` float NOT NULL,
  PRIMARY KEY  (`option_id`),
  CONSTRAINT `FK_gomage_navigation_attribute_option` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('eav_attribute_option')}` (`option_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$attribute_data = array(
        'group'             => 'General',
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Filter Image',
        'input'             => 'image',
        'class'             => '',
        'source'            => '',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    );

$installer->addAttribute('catalog_category', 'filter_image', $attribute_data);

$installer->endSetup(); 