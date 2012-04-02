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
 * Shutl Default Helper
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */

 
class Pod1_Shutl_Helper_Data extends Mage_Core_Helper_Abstract {
	
	protected $_code = 'shutl';


	public function getSelectedDate() {
		return Mage::getSingleton('core/session')->getShutlRawDate();
	}


	public function getSelectedTime() {
		return Mage::getSingleton('core/session')->getShutlTime();
	}

	public function getQuoteTimeout() {
		return  (string)Mage::getConfig()->getNode('default/time/quote_timeout');		
	}

	
	public function getLogo() {
		return  (string)Mage::getConfig()->getNode('default/cdn/logo_shutl');		
	}

	public function getShutlNow() {
		return  (string)Mage::getConfig()->getNode('default/cdn/logo_shutl_now');			
	}

	public function getShutlLater() {
		return  (string)Mage::getConfig()->getNode('default/cdn/logo_shutl_later');			
	}

	public function getCode() {
		return $this->_code;
	}

	public function isActive() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/active');		
	}

	public function getTitle() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/title');	
	}
	
	public function getServices() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/services');
	}

	public function getApiUrl() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/apiurl');		
	}
	
	public function getOpeningTime() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/storeopen');			
	}
	public function getClosingTime() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/storeclose');			
	}
	
	
	public function getDeliveryWindow() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/deliverywindow');		

	}

	public function getAboutUrl() {
		return  (string)Mage::getConfig()->getNode('default/text/shutl_about');			
	}

	public function getShutlLaterUrl() {
		return  (string)Mage::getConfig()->getNode('default/text/shutl_later');			
	}

	public function getShutlNowUrl() {
		return  (string)Mage::getConfig()->getNode('default/text/shutl_now');			
	}
	
	public function getClientId() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/clientid');		
	}
	
	public function getApiKey() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/apikey');
	}
	
	public function getDefaultVehicle() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/defaultvehicle');
	}

	public function getStoreClosedMethodLabel() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/store_closed_method_label');	
	}
	public function getMethodLabel() {
		return Mage::getStoreConfig('carriers/' . $this->_code . '/method_label');	
	}

	public function query($url, $postData = null, $headers = null, $contentType = null, $forcePost = false) {
	
		try {


			if (is_null($contentType) || is_null($postData)) {
				$curl = curl_init($url);
				if ($forcePost) {
					curl_setopt($curl, CURLOPT_POST, 1);			
				}
				
									
			} else {
				$curl = curl_init($url . '?' . urlencode($postData));			
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			}
			
			if (!is_null($headers)) {
			
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			
			}
			
			if (strpos($url, 'https') >= 0) {
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			}
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($curl);
	 		return $response;		
		} catch(Exception $e) {
			Mage::logException($e);
			return false;
		}
	}

	public function getCoords($postCode) { 
		$url = 'http://maps.google.com/maps/geo?&output=xml&oe=utf8&sensor=false&q=' . urlencode($postCode);
		$response = $this->query($url);
		if (!$response) { return array(); }
		
		try {

			$xml = simplexml_load_string($response);

			$coords = (string)$xml->Response->Placemark->Point->coordinates;
			$address = (string)$xml->Response->Placemark->address;
			Mage::log($response, null, 'shutl_google_maps.log');
			
			$coords = explode(',', $coords);
			$return = array(
				'latitude'	=> $coords[0],
				'longitude'	=> $coords[1],
				'address'	=> $address
			);
			return $return;
			
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
	}
	public function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit) { 
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist); 
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		
		if ($unit == "K") {
		  return ($miles * 1.609344); 
		} else if ($unit == "N") {
		  return ($miles * 0.8684);
		} else {
		  return $miles;
		}
	}	

	public function getAboutHtml() {
	
		$url = $this->getAboutUrl();
		$html = $this->query($url);
		
		$body = array();
		preg_match("/<body.*\/body>/s", $html, $body);
		if (sizeof($body) == 0) {
			return false;
		}
		
		$body = str_replace(array('<body>', '</body>'), '', $body[0]);
		
		return $body;
	}
	

	public function getShutlLaterHtml() {

		$url = $this->getShutlLaterUrl();
		$html = $this->query($url);
		
		$body = array();
		preg_match("/<body.*\/body>/s", $html, $body);
		if (sizeof($body) == 0) {
			return false;
		}
		
		$body = str_replace(array('<body>', '</body>', '<p>', '</p>'), '', $body[0]);		
		return $body;
	}


	public function getShutlNowHtml() {
	
		$url = $this->getShutlNowUrl();
		$html = $this->query($url);
		
		$body = array();
		preg_match("/<body.*\/body>/s", $html, $body);
		if (sizeof($body) == 0) {
			return false;
		}
		
		$body = str_replace(array('<body>', '</body>', '<p>', '</p>'), '', $body[0]);
		return $body;
	}
	
	public function getErrorMessage($errorCode) {
		$defaultMessage = Mage::getStoreConfig('carriers/' . $this->_code . '/error_message');
		$codeMessages	= unserialize(Mage::getStoreConfig('carriers/' . $this->_code . '/error_code_messages'));
		foreach($codeMessages as $k => $v) {
			if ($v['error_code'] == $errorCode) {
				return $v['error_message'];
			}
		}
		return $defaultMessage;

	
	}

	protected $_coordsCache = array();

	public function getCarrierSla() {
		
		
		$response = $this->query(
			$this->getApiUrl() . 'sla/'
			, null
			, array('ClientID: ' . $this->getClientId(), 'APIKey: ' . $this->getApiKey())
		);
		
		$xml = simplexml_load_string($response);		
		
		try {

			$attributes = $xml->attributes();
			if ($attributes['success'] == 0) {
				$error = $xml->error->attributes();
				$errorNumber = (string)$error->code;
				Mage::getSingleton('core/session')->addError($this->getErrorMessage($errorNumber));				
				return false;
			}
			$slaAttributes = $xml->sla->attributes();
			$return = array(
                    'pickup_sla' => $slaAttributes['pickup_sla'],
                    'delivery_sla' => $slaAttributes['delivery_sla'],
                    'carrier_holding_period' => $slaAttributes['carrier_holding_period']
                );
            return $return;
		} catch (Exception $e) {
			Mage::logException($e->getMessage());
			return false;
		}
		
	}

	public function findClosestStore($deliveryPostcode, $sku) {
		$deliveryCoords = Mage::helper('shutl')->getCoords($deliveryPostcode);
		
		if (!$deliveryCoords) { return false; }
		if (strpos(strtolower($deliveryCoords['address']), 'london') < 0) { return -1; }

		$stockLevels = Mage::getModel('shutl/stocklevel')->getCollection();
		$stockLevels->getSelect()->where('(sku IN (\'' . implode('\',\'', $sku)  . '\')) AND (stock_level > 0)');
		$stockLevels->getSelect()->group('shutl_stocklocation_id');
		
		$stores = array();
		
		foreach ($stockLevels as $stockLevel) {
			$store = Mage::getModel('shutl/stocklocation')->load($stockLevel->getShutlStocklocationId());
			
			if (!array_key_exists($store->getPostcode(), $this->_coordsCache)) {
				$this->_coordsCache[$store->getPostcode()] = Mage::helper('shutl')->getCoords($store->getPostcode());			
			}
			$coords = $this->_coordsCache[$store->getPostcode()];
			
			if (!$coords) { continue; }			
			$distance = Mage::helper('shutl')->calculateDistance(
				$coords['latitude'], $coords['longitude'], $deliveryCoords['latitude'], $deliveryCoords['longitude'], 'k'
			);
			
			$stores[] = array(
				'reference'	=> $store->getShutlReference(),
				'postcode'	=> $store->getPostcode(),
				'distance'	=> $distance
			);
		}
		if (sizeof($stores) == 0) { return false; }
		$sortArray = array();
		foreach($stores as $store){
		    foreach($store as $key=>$value){
		        if(!isset($sortArray[$key])){
		            $sortArray[$key] = array();
		        }
		        $sortArray[$key][] = $value;
		    }
		}
		
		array_multisort($sortArray['distance'],SORT_ASC,$stores); 
		$closestStore 				= $stores[0];
		//Before returning the closest store, we need to get the timezone offset
		$storeInfo 					= $this->getStoreInfo($closestStore['reference']);
		if (!$storeInfo) { return false; }
		$timeAttributes 			= $storeInfo->time->attributes();
		$timezone 					= new DateTimeZone((string)$timeAttributes['timezone']);		
		$timezone					= $timezone->getOffset(new DateTime("now", $timezone)) / 3600;
		
		if (!is_numeric($timezone)) { $timezone = 0; }

		if ($timezone == 0){
			$timezone = '+00:00';
		} elseif ($timezone > -10 && $timezone < 0) {
			$timezone = '-0' . ($timezone * -1) . ':00';
		} elseif ($timezone > 0 && $timezone < 10) {
			$timezone = '+0' . $timezone . ':00';
		} elseif ($timezone > 10){
			$timezone = '+' . $timezone . ':00';
		} else {
			$timezone = $timezone . ':00';
		}

		$closestStore['timezone'] 	= $timezone;
		return $closestStore;
	
	}    
	
	public function getDateFormat() {
		$_mappings = array(
			'MM'	=> 'm',
			'mm'	=> 'm',
			'm'		=> 'm',
			'M'		=> 'm',
			'd'		=> 'd',	
			'dd'	=> 'd',
			'yyyy'	=> 'Y',
			'y'		=> 'Y',
			'Y'		=> 'Y',
			'yy'	=> 'Y'
		);
		try {
			$mageFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
			$separator = $this->findDateSeparator($mageFormat);
			$pieces = explode($separator, $mageFormat);
			$return = array();
			foreach ($pieces as $piece) {
				$return[] = $_mappings[$piece];
			}
			return implode($separator, $return);
		
		} catch (Exception $e) {
			Mage::logException($e->getMassage());
			return ('Y-m-d');
		}
	}
	
	public function findDateSeparator($string) {
		$separator = array();
		if (!preg_match("/[^0-9a-z]/i", $string, $separator)) {
			return false;
		} else {
			return $separator[0];
		}
	}

	public function convertDate($date) {

		if (!$separator = $this->findDateSeparator($date)) { return $date; }

		$fromFormat	= explode($this->findDateSeparator($this->getDateFormat()), $this->getDateFormat());
		$dateParts	= explode($separator, $date);
		$toFormat	= explode('-', 'Y-m-d');
		$convertedDate = array();
		
		for ($i = 0; $i < sizeof($toFormat); $i++) {
			$pos = array_search($toFormat[$i], $fromFormat);
			if ($pos !== false) {
				$convertedDate[] = $dateParts[$pos];
			} else {
				return false;
			}
		}
		return implode('-', $convertedDate);
	
	}
	
	public function getStoreInfo($storeReference) {
	
		$response =  Mage::helper('shutl')->query(
			$this->getApiUrl() . 'store/' . $storeReference
			, null
			, array('ClientID: ' . $this->getClientId(), 'APIKey: ' . $this->getApiKey())
		);
		
		try {
			$xml = simplexml_load_string($response);		

			$attributes = $xml->attributes();
			if ($attributes['success'] == 0) {
				$error = $xml->error->attributes();
				$errorNumber = (string)$error->code;
				Mage::getSingleton('core/session')->addError($this->getErrorMessage($errorNumber));
				return false;
			}
			return $xml->store;		
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
	
	}
	
	public function formatTimeSlot($i) {
		$from = ($i >= 10) ? $i : '0' . $i;
		if ($i == 23) {
			$to = '00';
		} else {
			$to = (($i+1) >= 10) ? ($i+1) : '0' .($i+1);	
		}
		return $from . ':00 - ' . $to . ':00';
	}
	
	public function getCalendarInfo($startDate, $lastSelectableDate, $today, $counter) {
	

		$currentDate = date('Y-m-d-M-j-N', strtotime($startDate . ' +'.$counter.' day'));
		$currentDateParts = explode('-', $currentDate);
		$counter++;
		$class = "";
		if ($currentDate == $today) { $class .= " today selected"; }
		if ($currentDate >= $today && $currentDate <= $lastSelectableDate) { $class .= " selectable"; }
		if (strlen($class) > 0) { $class = ' class="' . $class . '"'; }

		return array(
			'class'	=> $class,
			'current_date' => $currentDate,
			'day'	=> $currentDateParts[4],
			'date'	=> date($this->getDateFormat(), mktime(0,0,0,$currentDateParts[1],$currentDateParts[2],$currentDateParts[0]))
		);
	}
	
}