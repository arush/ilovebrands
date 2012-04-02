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
 * Shutl Export Stock Location Model
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */

class Pod1_Shutl_Model_System_Config_Backend_Stocklocation extends Mage_Core_Model_Config_Data {
    public function _afterSave() {
    
    
		$_helper = Mage::helper('shutl');
		$return = array();
	
		$vehicleList = $_helper->query(
			Mage::helper('shutl')->getApiUrl() . 'vehicles'
			, null
			, array('ClientID: ' . $_POST['groups']['shutl']['fields']['clientid']['value'], 'APIKey: ' . $_POST['groups']['shutl']['fields']['apikey']['value'])
		);

		$xml = simplexml_load_string($vehicleList);
		if (!$xml) {
			Mage::getSingleton('core/session')->addError('Could not receive vehicle list.');			
			return;
		}
		try {
			
			$success = $xml->attributes();
			if ((string)$success['success'] != '1') {
				$error = $xml->error->attributes();
				$errorNumber = (string)$error->code;
				Mage::getSingleton('core/session')->addError($_helper->getErrorMessage($errorNumber));
			
			}
		} catch(Exception $e) {
			Mage::getSingleton('core/session')->addError($_helper->getErrorMessage($errorNumber));
			Mage::log($e->getMessage(), null, 'shutl_stocklocation.log');		
			Mage::log($vehicleList, null, 'shutl_stocklocation.log');		
		}
		Mage::getResourceModel('shutl/stocklocation')->uploadAndImport($this);
    }
}