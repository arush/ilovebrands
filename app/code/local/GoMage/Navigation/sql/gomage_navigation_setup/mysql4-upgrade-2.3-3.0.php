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
 * @since        Release available since Release 3.0
 */

$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_category', 'navigation_label_menubar');
$installer->removeAttribute('catalog_category', 'navigation_column');
$installer->removeAttribute('catalog_category', 'navigation_pw_width');
$installer->removeAttribute('catalog_category', 'navigation_image');
$installer->removeAttribute('catalog_category', 'navigation_image_position');
$installer->removeAttribute('catalog_category', 'navigation_image_width');
$installer->removeAttribute('catalog_category', 'navigation_image_height');
$installer->removeAttribute('catalog_category', 'navigation_label_columns');

// start Plain Window Settings
$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Template',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_template',
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
		'sort_order'		=> 10,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_template', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Window Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 20,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_bgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Plain Window Width, px',
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
		'sort_order'		=> 30,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_width', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Plain Window Height, px',
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
		'sort_order'		=> 40,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_height', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Border Size, px',
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
		'sort_order'		=> 50,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_bsize', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Border Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 60,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_bcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Columns Width, px',
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
		'sort_order'		=> 70,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_cwidth', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Column Indent (left side), px',
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
		'sort_order'		=> 80,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_c_indentl', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Column Indent (right side), px',
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
		'sort_order'		=> 90,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_c_indentr', $attribute_data);

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
		'sort_order'		=> 100,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_column', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => 'catalog/category_attribute_backend_image',
        'frontend'          => '',
        'label'             => 'Plain Image',
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
		'sort_order'		=> 110,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_s_img', $attribute_data);
// end Plain Window Settings

// start First Level Subcategory Settings
$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 130,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_tcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mouse Over Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 140,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_otcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category Text Size',
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
		'sort_order'		=> 150,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_tsize', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 160,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_bgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mouse Over Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 170,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_obgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category View',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_attribute_view',
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
		'sort_order'		=> 180,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_view', $attribute_data);

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
		'sort_order'		=> 190,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_ipos', $attribute_data);

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
		'sort_order'		=> 200,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_iwidth', $attribute_data);

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
		'sort_order'		=> 210,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_fl_iheight', $attribute_data);
// end First Level Subcategory Settings

// start Second Level Subcategory Settings
$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 230,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_tcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mouse Over Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 240,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_otcolor', $attribute_data);


$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category Text Size',
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
		'sort_order'		=> 250,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_tsize', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Category View',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'gomage_navigation/adminhtml_system_config_source_category_attribute_view',
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
		'sort_order'		=> 260,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_view', $attribute_data);

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
		'sort_order'		=> 270,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_ipos', $attribute_data);

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
		'sort_order'		=> 280,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_iwidth', $attribute_data);

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
		'sort_order'		=> 290,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_sl_iheight', $attribute_data);
// end Second Level Subcategory Settings

// start Offer Block Settings
$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Show Offer Block',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
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
		'sort_order'		=> 310,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_show', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Offer Block Position',
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
		'sort_order'		=> 320,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_pos', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Offer Block Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 330,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_bgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Offer Block Width, px',
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
		'sort_order'		=> 340,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_width', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Offer Block Height, px',
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
		'sort_order'		=> 350,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_height', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Offer Description',
        'input'             => 'textarea',
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
		'sort_order'		=> 360,			 
    );
$installer->addAttribute('catalog_category', 'navigation_pw_ob_desc', $attribute_data);
// end Offer Block Settings

//start Menu Bar Settings
$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Border Size, px',
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
		'sort_order'		=> 370,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_bsize', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Border Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 380,
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_bcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 390,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_bgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mouse Over Background Color',
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
		'note'				=> 'e.g. "ffffff", "000"',	
		'sort_order'		=> 395,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_obgcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 400,		
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_tcolor', $attribute_data);

$attribute_data = array(
        'group'             => 'Advanced Navigation',
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Mouse Over Text Color',
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
		'note'				=> 'e.g. "ffffff", "000"',
		'sort_order'		=> 410,	
    );
$installer->addAttribute('catalog_category', 'navigation_pw_m_otcolor', $attribute_data);
//end Menu Bar Settings

$installer->run("
ALTER TABLE `{$this->getTable('gomage_navigation_attribute')}`
  DROP COLUMN `popup_text`;
");

$installer->run("
ALTER TABLE `{$this->getTable('gomage_navigation_attribute')}`
  ADD COLUMN `visible_options` int(3) NOT NULL default '0';
");

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('gomage_navigation_attribute_store')}` (
  `id` smallint(6) NOT NULL auto_increment,	
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) NOT NULL default '0',
  `popup_text` TEXT,
  PRIMARY KEY  (`id`),
  CONSTRAINT `FK_gomage_navigation_attribute_store` FOREIGN KEY (`attribute_id`) REFERENCES `{$this->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");

$category_entity_type_id = $installer->getEntityTypeId('catalog_category');
$installer->updateAttribute($category_entity_type_id, 'navigation_pw_ob_desc', 'is_wysiwyg_enabled', 1); 

$pageTable = $installer->getTable('cms/page');
$installer->getConnection()->addColumn($pageTable, 'navigation_category_id',
    "int(10) NOT NULL DEFAULT 0");
    
$installer->endSetup(); 