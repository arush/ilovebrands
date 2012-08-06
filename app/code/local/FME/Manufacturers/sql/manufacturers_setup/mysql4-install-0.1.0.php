<?php
/**
 * Manufacturers extension 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME 
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('manufacturers')};
CREATE TABLE {$this->getTable('manufacturers')} (
	`manufacturers_id` int(11) unsigned NOT NULL auto_increment,  
	 `m_name` varchar(255) NOT NULL default '',                    
	 `m_website` varchar(255) default '',                          
	 `m_address` text,                                             
	 `m_logo` varchar(255) default '',                                              
	 `m_featured` int(11) default '0',                             
	 `m_contact_name` varchar(255) default '',                     
	 `m_contact_phone` varchar(255) default '',                    
	 `m_contact_fax` varchar(255) default '',                      
	 `m_contact_email` varchar(255) default '',                    
	 `m_contact_address` varchar(255) default '',                  
	 `m_details` text,                                             
	 `m_product_ids` text,   
	 `identifier` varchar(255) default NULL,
	 `m_manufacturer_page_title` varchar(255) default NULL,        
     `m_manufacturer_meta_keywords` text,                          
     `m_manufacturer_meta_description` text,
	 `status` tinyint(11) default NULL,                            
	 `created_time` datetime default NULL,                         
	 `update_time` datetime default NULL,
	 `option_id` int(10) default NULL,
	 PRIMARY KEY  (`manufacturers_id`)                             
	) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('manufacturers_store')};
CREATE TABLE {$this->getTable('manufacturers_store')} (                                
   `manufacturers_id` int(11) unsigned NOT NULL,                      
   `store_id` smallint(5) unsigned NOT NULL,                          
   PRIMARY KEY  (`manufacturers_id`,`store_id`),                      
   KEY `FK_MANUFACTURERS_STORE_STORE` (`store_id`)                    
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Manufacturers Stores';

DROP TABLE IF EXISTS {$this->getTable('manufacturers_products')};
CREATE TABLE {$this->getTable('manufacturers_products')} (                                
	`manufacturers_id` int(11) NOT NULL,                                 
	`product_id` int(20) NOT NULL,                                                      
	PRIMARY KEY  (`manufacturers_id`,`product_id`),                      
	KEY `FK_MANUFACTURERS_PRODUCTS` (`product_id`)                       
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Manufacturers Products';

    ");

$installer->setConfigData('manufacturers/manufacturers/layout','page/2columns-left.phtml');
$installer->setConfigData('manufacturers/manufacturers/page_title','Shop By Brands');
$installer->setConfigData('manufacturers/manufacturers/main_heading','Brands');
$installer->setConfigData('manufacturers/manufacturers/identifier','brands');
$installer->setConfigData('manufacturers/manufacturers/items_per_page','10');
$installer->setConfigData('manufacturers/manufacturers/show_manufacturers_logo','1');
$installer->setConfigData('manufacturers/manufacturers/show_manufacturers_with_alphabets','0');
$installer->setConfigData('manufacturers/manufacturers/show_manufacturers_view_by','0');
$installer->setConfigData('manufacturers/manufacturers/logo_width','150');
$installer->setConfigData('manufacturers/manufacturers/logo_height','150');
$installer->setConfigData('manufacturers/manufacturers/logo_background_Color','255,255,255');
$installer->setConfigData('manufacturers/manufacturers/limit_description','255');
$installer->setConfigData('manufacturers/manufacturers/meta_keywords','Shop by Brands, Shop by Manufacturers');
$installer->setConfigData('manufacturers/manufacturers/meta_description','Shop by Brands, Shop by Manufacturers');
$installer->setConfigData('manufacturers/manufacturers/sort_manufacturers','main_table.m_name');
$installer->setConfigData('manufacturers/manufacturers/order_manufacturers_by','asc');

$installer->setConfigData('manufacturers/productpagelogo/enabled','1');
$installer->setConfigData('manufacturers/productpagelogo/product_page_logo_heading','Brand');
$installer->setConfigData('manufacturers/productpagelogo/product_logo_width','100');
$installer->setConfigData('manufacturers/productpagelogo/product_logo_height','100');
$installer->setConfigData('manufacturers/productpagelogo/logo_background_Color','255,255,255');

$installer->setConfigData('manufacturers/leftnav/viewtype','ddl');
$installer->setConfigData('manufacturers/leftnav/numberofmanufacturers','0');

$installer->setConfigData('manufacturers/featuredmanufacturers/main_heading','ddl');
$installer->setConfigData('manufacturers/featuredmanufacturers/width','0');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_width','130');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_height','130');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_background_Color','255,255,255');

$installer->setConfigData('manufacturers/seo/url_suffix','.html');

$installer->setConfigData('manufacturers/importfrommagento/attribute_code','manufacturer');

$installer->setConfigData('manufacturers/productpagelogo/product_page_brand_link','');
$installer->setConfigData('manufacturers/leftnav/sort_left_manufacturers','main_table.m_name');
$installer->setConfigData('manufacturers/leftnav/order_left_manufacturers_by','asc');
$installer->setConfigData('manufacturers/featuredmanufacturers/sort_featured_manufacturers','main_table.m_name');
$installer->setConfigData('manufacturers/featuredmanufacturers/order_featured_manufacturers_by','asc');

$installer->endSetup(); 