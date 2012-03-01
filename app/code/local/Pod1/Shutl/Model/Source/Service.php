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
 * Shutl service source
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
class Pod1_Shutl_Model_Source_Service {
	/**
	 * Return source option array
	 *
	 * @return array
	 */
	public function toOptionArray() {
	
		$return = array();
			$return[] = array('value' => 'shutl_now', 'label' => Mage::helper('core')->__('Shutl Now'));	
			$return[] = array('value' => 'shutl_later', 'label' => Mage::helper('core')->__('Shutl Later'));	
		return $return;
    }
}



