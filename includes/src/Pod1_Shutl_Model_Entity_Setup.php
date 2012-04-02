<?php

/**
 * Pod1 Shutl extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @copyright  Copyright (c) 2010 Pod1 (http://www.pod1.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Pod1_Shutl_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup {

    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_configurable'           => $this->_getValue($attr, 'is_configurable', 1)
        ));
        return $data;
    }

    public function getDefaultEntities() {
        return array(
			'catalog_product' => array(
                'entity_model'      			=> 'catalog/product',
               	'attribute_model'   			=> 'catalog/resource_eav_attribute',
                'table'             			=> 'catalog/product',
                'additional_attribute_table' 	=> 'catalog/eav_attribute',
                'entity_attribute_collection' 	=> 'catalog/product_attribute_collection',
                'attributes'        => array(
                    'pod1_shutl' => array(
                        'group'             		=> 'Delivery Options',
                        'type'              		=> 'varchar',
                        'backend'           		=> '',
                        'label'             		=> 'Shutl Available',
                        'frontend'          		=> '',
                        'table'             		=> '',
                        'input'             		=> 'select',
                        'class'             		=> '',
                        'source'            		=> 'eav/entity_attribute_source_boolean',
                        'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           		=> true,
                        'required'          		=> false,
                        'user_defined'      		=> true,
                        'default'          	 		=> '',
                        'searchable'        		=> false,
                        'filterable'        		=> false,
                        'comparable'        		=> false,
                        'visible_on_front'  		=> false,
                        'unique'           		 	=> false,
                        'used_in_product_listing' 	=> true,
                        'is_configurable'			=> false                        
                    ),
                    'pod1_shutl_default_vehicle' => array(
                        'group'             		=> 'Delivery Options',
                        'type'              		=> 'varchar',
                        'backend'           		=> '',
                        'label'             		=> 'Default Vehicle',
                        'frontend'          		=> '',
                        'table'             		=> '',
                        'input'             		=> 'select',
                        'class'             		=> '',
                        'source'            		=> 'shutl/source_vehicle',
                        'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           		=> true,
                        'required'          		=> false,
                        'user_defined'      		=> true,
                        'default'          	 		=> '',
                        'searchable'        		=> false,
                        'filterable'        		=> false,
                        'comparable'        		=> false,
                        'visible_on_front'  		=> false,
                        'unique'           		 	=> false,
                        'used_in_product_listing' 	=> true,
                        'is_configurable'			=> false
                        
                    ),                    
                    'length' => array(
                        'group'             		=> 'Dimensions',
                        'type'              		=> 'varchar',
                        'backend'           		=> '',
                        'label'             		=> 'Length (in cm)',
                        'frontend'          		=> '',
                        'table'             		=> '',
                        'input'             		=> 'text',
                        'class'             		=> 'validate-number',
                        'source'            		=> '',
                        'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           		=> true,
                        'required'          		=> false,
                        'user_defined'      		=> true,
                        'default'          	 		=> '',
                        'searchable'        		=> false,
                        'filterable'        		=> false,
                        'comparable'        		=> false,
                        'visible_on_front'  		=> false,
                        'unique'           		 	=> false,
                        'used_in_product_listing' 	=> true
                    ),
                    'width' => array(
                        'group'             		=> 'Dimensions',
                        'type'              		=> 'varchar',
                        'backend'           		=> '',
                        'label'             		=> 'Width (in cm)',
                        'frontend'          		=> '',
                        'table'             		=> '',
                        'input'             		=> 'text',
                        'class'             		=> 'validate-number',
                        'source'            		=> '',
                        'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           		=> true,
                        'required'          		=> false,
                        'user_defined'      		=> true,
                        'default'          	 		=> '',
                        'searchable'        		=> false,
                        'filterable'        		=> false,
                        'comparable'        		=> false,
                        'visible_on_front'  		=> false,
                        'unique'           		 	=> false,
                        'used_in_product_listing' 	=> true
                    ),
                    'height' => array(
                        'group'             		=> 'Dimensions',
                        'type'              		=> 'varchar',
                        'backend'           		=> '',
                        'label'             		=> 'Height (in cm)',
						'frontend'          		=> '',
                        'table'             		=> '',
                        'input'             		=> 'text',
                        'class'             		=> 'validate-number',
                        'source'            		=> '',
                        'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           		=> true,
                        'required'          		=> false,
                        'user_defined'      		=> true,
                        'default'          	 		=> '',
                        'searchable'        		=> false,
                        'filterable'        		=> false,
                        'comparable'        		=> false,
                        'visible_on_front'  		=> false,
                        'unique'           		 	=> false,
                        'used_in_product_listing' 	=> true
                    )                    
                    
				)           
			)
      	);
    }
}