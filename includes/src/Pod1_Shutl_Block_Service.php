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

/**
 * Shutl Service Block
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */

class Pod1_Shutl_Block_Service extends Mage_Core_Block_Template {


	protected function _getProduct() {
		return Mage::registry('product');
	}
	
	protected function _getServices() {
	
    	$return = array();
    	$services = explode(',', Mage::helper('shutl')->getServices());
    	$serviceOptions = Mage::getSingleton('shutl/source_service')->toOptionArray();

    	if (!$serviceOptions) { return $return; }

    	if (is_array($serviceOptions)) {
    		foreach($serviceOptions as $k => $v) {
				$active = false;
		   		foreach ($services as $service) {
    				if ($service == $v['value']) {
	    				$active = true;
						break;
    				}
    			}
   				$return[] = array('value' => $v['value'], 'label' => $v['label'], 'active' => $active);    			
    		}
    	}
    	
        return $return;
	
	}

}