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
 * @since        Release available since Release 1.1
 */

$installer = $this;

$installer->startSetup();

$installer->run("");

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '<b>Menu Bar Navigation</b>',
        'input'             => 'label',
        'class'             => '',
        'source'            => '',
        'global'            => false,
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
$installer->addAttribute('catalog_category', 'navigation_label_menubar', $attribute_data);


$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Select Column',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_column',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => 1,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    );
$installer->addAttribute('catalog_category', 'navigation_column', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Plain Window Width, px',
        'input'             => 'text',
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
$installer->addAttribute('catalog_category', 'navigation_pw_width', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Image for Plain Window',
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
$installer->addAttribute('catalog_category', 'navigation_image', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Image Position',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_image_position',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => 0,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    );
$installer->addAttribute('catalog_category', 'navigation_image_position', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Image Width, px',
        'input'             => 'text',
        'class'             => '',
        'frontend_class'    => 'gomage-validate-number',         
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
$installer->addAttribute('catalog_category', 'navigation_image_width', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Image Height, px',
        'input'             => 'text',
        'class'             => '',
        'frontend_class'    => 'gomage-validate-number',
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
$installer->addAttribute('catalog_category', 'navigation_image_height', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '<b>Left / Right Column Navigation</b>',
        'input'             => 'label',
        'class'             => '',
        'source'            => '',
        'global'            => false,
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
$installer->addAttribute('catalog_category', 'navigation_label_columns', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Select Column',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_column',
        'global'            => true,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'default'           => 1,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
    );
$installer->addAttribute('catalog_category', 'navigation_column_side', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Plain Window Width, px',
        'input'             => 'text',
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
$installer->addAttribute('catalog_category', 'navigation_pw_side_width', $attribute_data);

$pageTable = $installer->getTable('cms/page');
$installer->getConnection()->addColumn($pageTable, 'navigation_left_column',
    "TINYINT(1) NOT NULL DEFAULT 0");
$installer->getConnection()->addColumn($pageTable, 'navigation_right_column',
    "TINYINT(1) NOT NULL DEFAULT 0");  

$installer->getConnection()->addColumn($this->getTable('gomage_navigation_attribute'), 'filter_reset',
    "TINYINT(1) NOT NULL DEFAULT 0");

$attribute_group_id = $installer->getConnection()->fetchOne("        
    select eea1.attribute_group_id 
    from {$this->getTable('eav_attribute')} ea1
    inner join {$this->getTable('eav_entity_attribute')} eea1 on ea1.attribute_id = eea1.attribute_id
    where ea1.attribute_code = 'navigation_column';    
");

$attribute_id = $installer->getConnection()->fetchOne("        
    select attribute_id from {$this->getTable('eav_attribute')} 
	where attribute_code = 'filter_image';    
"); 

if ($attribute_group_id && $attribute_id)
{
    $installer->run("UPDATE {$this->getTable('eav_entity_attribute')}
                SET attribute_group_id = {$attribute_group_id}
                WHERE attribute_id = {$attribute_id}");
    $installer->run("UPDATE {$this->getTable('eav_attribute')}
                SET frontend_label = 'Category Image'
                WHERE attribute_id = {$attribute_id}");  
}

$installer->getConnection()->addColumn($this->getTable('gomage_navigation_attribute'), 'is_ajax',
    "TINYINT(1) NOT NULL DEFAULT 0");

$installer->endSetup(); 