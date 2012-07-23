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
 * @since        Release available since Release 2.1
 */

$installer = $this;

$installer->startSetup();

$attribute_id = $installer->getConnection()->fetchOne("        
    select attribute_id from {$this->getTable('eav_attribute')} 
	where attribute_code = 'navigation_plain_window_width';    
"); 

if ($attribute_id){
    $installer->run("UPDATE {$this->getTable('eav_attribute')}
                SET attribute_code = 'navigation_pw_width'
                WHERE attribute_id = {$attribute_id}");
}

$attribute_id = $installer->getConnection()->fetchOne("        
    select attribute_id from {$this->getTable('eav_attribute')} 
	where attribute_code = 'navigation_plain_window_side_width';    
"); 

if ($attribute_id){
    $installer->run("UPDATE {$this->getTable('eav_attribute')}
                SET attribute_code = 'navigation_pw_side_width'
                WHERE attribute_id = {$attribute_id}");
}

$installer->getConnection()->addColumn($this->getTable('gomage_navigation_attribute'), 'inblock_height',
    "int(3) NOT NULL DEFAULT 150");

$installer->getConnection()->addColumn($this->getTable('gomage_navigation_attribute'), 'filter_button',
    "TINYINT(1) NOT NULL DEFAULT 1");
    
$installer->endSetup(); 