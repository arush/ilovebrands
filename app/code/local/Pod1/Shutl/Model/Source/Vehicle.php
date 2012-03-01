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
 * Shutl vehicle source
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
class Pod1_Shutl_Model_Source_Vehicle extends Mage_Eav_Model_Entity_Attribute_Source_Table {
	/**
	 * Return source option array
	 *
	 * @return array
	 */
	public function getAllOptions() {
		return $this->toOptionArray();
	}
	
	public function getOptionText($value) {
		$options = $this->toOptionArray();
		foreach ($options as $option) {
			if(is_array($value)){
				if (in_array($option['value'],$value)) {
					return $option['label'];
				}
			}
			else{
				if ($option['value']==$value) {
					return $option['label'];
				}
			}

		}
		return false;		
	}

	public function toOptionArray() {
	

		$_helper = Mage::helper('shutl');
		$return = array();
	
		$vehicleList = $_helper->query(
			Mage::helper('shutl')->getApiUrl() . 'vehicles'
			, null
			, array('ClientID: ' . Mage::helper('shutl')->getClientId(), 'APIKey: ' . Mage::helper('shutl')->getApiKey())
		);

		if ($vehicleList === false) { return $return; }
		
		$xml = simplexml_load_string($vehicleList);
		$success = $xml->attributes();
		if ((string)$success['success'] != '1') {
			return $return;
		}
		

		$xml = simplexml_load_string($vehicleList);

		foreach($xml->vehicle_list->vehicle as $vehicle) {
			$attributes = $vehicle->attributes();
			
			$return[] = array('value' => (string)$attributes['internal_code'], 'label' => Mage::helper('core')->__((string)$attributes['name']));
		}
		return $return;
    }
}



