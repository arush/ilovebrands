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

$installer->setConfigData('manufacturers/manufacturers/layout','page/2columns-left.phtml');
$installer->setConfigData('manufacturers/manufacturers/logo_width','150');
$installer->setConfigData('manufacturers/manufacturers/logo_height','150');
$installer->setConfigData('manufacturers/manufacturers/logo_background_Color','255,255,255');

$installer->setConfigData('manufacturers/productpagelogo/product_logo_width','100');
$installer->setConfigData('manufacturers/productpagelogo/product_logo_height','100');
$installer->setConfigData('manufacturers/productpagelogo/logo_background_Color','255,255,255');

$installer->setConfigData('manufacturers/featuredmanufacturers/main_heading','Featured Brands');
$installer->setConfigData('manufacturers/featuredmanufacturers/width','610');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_width','130');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_height','130');
$installer->setConfigData('manufacturers/featuredmanufacturers/logo_background_Color','255,255,255');

$installer->setConfigData('manufacturers/manufacturers/sort_manufacturers','main_table.m_name');
$installer->setConfigData('manufacturers/manufacturers/order_manufacturers_by','asc');

$installer->endSetup(); 