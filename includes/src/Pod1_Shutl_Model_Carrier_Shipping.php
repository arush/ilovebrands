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
 * Shutl Carrier Model
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
 
class Pod1_Shutl_Model_Carrier_Shipping 
	extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {


	public function isTrackingAvailable() {
		return false;
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		$_helper = Mage::helper('shutl');

		$result = Mage::getModel('shipping/rate_result');		
	    if (!$_helper->isActive()) { return $result; }

		Mage::getSingleton('core/session')->setShutl('');		
		$code = $_helper->getCode();
		$title = $_helper->getTitle();

		if (Mage::getSingleton('core/session')->getShutlTime()) {
			$request['time'] = Mage::getSingleton('core/session')->getShutlTime();
		}

		if (!$request['date'] && Mage::getSingleton('core/session')->getShutlDate()) {

			$request['date'] = Mage::getSingleton('core/session')->getShutlDate();
		}		
		if (Mage::getSingleton('core/session')->getShutlMethod()) {
			$request['method'] = Mage::getSingleton('core/session')->getShutlMethod();
		}				
		if (Mage::getSingleton('core/session')->getShutlPostcode() && !$request->getPostcode()) {
			$request['postcode'] = Mage::getSingleton('core/session')->getShutlPostcode();
		}				
		if ($request->getDestPostcode()) {
			$request['postcode'] = $request->getDestPostcode();
		}
		$request['time'] = urldecode($request['time']);
		if (!$request['time']) { $request['time'] = '12:00 - 13:00'; }
		$request['date'] = urldecode($request['date']);
		if (!$request['date']) { $request['date'] = date('d-m-Y'); }
		$request['method'] = urldecode($request['method']);
		$request['postcode'] = urldecode($request['postcode']);
		if (strtolower($request['postcode']) == 'enter postcode here') { return $result; }
     	$rates = $this->getRate($request);
		foreach ($rates as $rate) {
			Mage::getSingleton('core/session')->getMessages(true);
		
			$method = Mage::getModel('shipping/rate_result_method');		
			$method->setCarrier($code);
			$method->setCarrierTitle($title);
		    $method->setMethod($rate['method']);
    		$method->setMethodTitle($rate['method_title']);
	    	$method->setPrice(number_format($rate['method_price'], 4));
			$result->append($method);		
		
		}
		Mage::log($result, null, 'shutl_rates.log');
		return $result;
	} 
	
	public function getRate(Mage_Shipping_Model_Rate_Request $request) {

		$return = array();
		$methods = $this->getAllowedMethods();

		$_helper = Mage::helper('shutl');


		$quotes = array();


		foreach ($methods as $method) {
			if ($request['method'] && $method['value'] != $request['method']) { continue; }

			$after = $before = '';
			if ($method['value'] == 'shutl_later') {
				$times = explode(' - ', $request['time']);
				if (sizeof($times) != 2) {
					Mage::getSingleton('core/session')->addError('No delivery window selected for Shutl later quote.');
				}
				$calcDate = strtotime($request['date']);
				$after = str_replace(' ', 'T', date('Y-m-d ', $calcDate)) . $times[0] . ':00';
				$before = str_replace(' ', 'T', date('Y-m-d ', $calcDate)) . $times[1] . ':00';
			} else {
				$before = '';
				$after = '';
			}
			
			$method['collect_before']	= '';//$before;
			$method['collect_after']	= '';//$after;
			$method['deliver_before']	= $before;
			$method['deliver_after']	= $after;
			$price = $this->getQuote($request, $method);
			if ($method['value'] == 'shutl_later') {
				$laterCounter = 1;
			
				while ($laterCounter < 7 && !$price) {
					$calcDate = strtotime(date('Y-m-d', $calcDate) . ' +' . $laterCounter . ' day');					
					$request['time'] = '12:00 - 13:00';
					$method['deliver_before']	= date('Y-m-d\\T', $calcDate) . '13:00:00';
					$method['deliver_after']	= date('Y-m-d\\T', $calcDate) . '12:00:00';
					$laterCounter++;
					$price = $this->getQuote($request, $method);				

				}
			} 
			if (!$price) { continue; }
			Mage::log($request['quote_id'], null, 'shutl_quotes.log');
			$quotes[$method['value']]['magento_id'] = $price['magento_id'];
			$quotes[$method['value']]['shutl_id'] = (string)$price['quote_id'];

			$shutlDate = date('D jS F Y', strtotime($price['deliver_date']));
			$shutlDeliverAfter = substr($price['deliver_after'],0,5);
			$shutlDeliverBefore = substr($price['deliver_before'],0,5);
			$shutlMethod = $method['value'];
			$shutlMethodLabel = $method['label'];

			if ($method['value'] == 'shutl_now' && $_helper->getStoreClosedMethodLabel() && date('Y-m-d', strtotime($price['deliver_date'])) > date('Y-m-d')) {
				$methodLabel = $_helper->getStoreClosedMethodLabel();
			} else {
				$methodLabel = $_helper->getMethodLabel();
			}
			
			$methodLabel = str_replace(
				array('{shutl_method}', '{shutl_method_label}', '{shutl_date}', '{shutl_deliver_before}', '{shutl_deliver_after}'),
				array($shutlMethod, $shutlMethodLabel, $shutlDate, $shutlDeliverBefore, $shutlDeliverAfter),
				$methodLabel
			);
			
			$return[] = array(
				'method'		=> $method['value'],
				'method_title'	=> $methodLabel,
				'method_price'	=> $price['value'],
				'quote_id'		=> $price['quote_id']						
			);
		}
		Mage::getSingleton('core/session')->setShutl(serialize($quotes));
		return $return;
	}

	private function _quickQuoteXml($vars) {

		$template = '<?xml version="1.0" encoding="utf-8"?>
 			<ShutlGenerateQuickQuoteRequest timestamp="'.$vars['timestamp'].'">
				<quote reservation_number="'.$vars['magento_id'].'" total_retail_value="' .  $vars['total']. '" client_id="'. $vars['clientId'].'" vehicle_type="' . $vars['vehicle'] . '" total_weight="'.$vars['totalWeight'].'" total_volume="'.$vars['totalVolume'].'" category="6">
					<pickup>
						<segment sequence="1">
							<address store_reference="'.$vars['storeReference'].'" />
							<time collect_before="'.$vars['collectBefore'].'" collect_after="'.$vars['collectAfter'].'" />
						</segment>
					</pickup>
					<delivery>
						<segment sequence="1">
							<address postal_code="'.$vars['deliveryPostCode'].'" />
							<time deliver_before="'.$vars['deliverBefore'].'" deliver_after="'.$vars['deliverAfter'].'" />
						</segment>
					</delivery>
				</quote>
			</ShutlGenerateQuickQuoteRequest>';	
			Mage::log($template, null, 'shutl_templates.log');						
		return $template;	

	}

	private function _quoteXml($vars) {

		$pickupItems = '';
		$deliveryItems = '';
		foreach ($vars['items'] as $pickupItem) {
			$pickupItems .= '<pickup_item 
			 product_id="'.$pickupItem['sku'].'"
			 product_name="'.str_replace('&', '&amp;', $pickupItem['name']).'"
			 height="'.$pickupItem['height'].'"
			 length="'.$pickupItem['length'].'"
			 width="'.$pickupItem['width'].'"
			 weight="'.$pickupItem['weight'].'"
			 quantity="'.$pickupItem['quantity'].'"
			 retail_price="'.$pickupItem['price'].'"
			/>';
			$deliveryItems .= '<delivery_item product_id="'.$pickupItem['sku'].'" />';
		}

		$template = '<?xml version="1.0" encoding="utf-8"?>
 			<ShutlGenerateQuoteRequest timestamp="'.$vars['timestamp'].'">
				<quote reservation_number="'.$vars['magento_id'].'" total_retail_value="' .  $vars['total']. '" client_id="'. $vars['clientId'].'" vehicle_type="' . $vars['vehicle'] . '" total_weight="'.$vars['totalWeight'].'" total_volume="'.$vars['totalVolume'].'" category="6">
					<customer name="'.$vars['customerName'].'" email="'.$vars['customerEmail'].'" phone="'.$vars['customerPhone'].'" />
					<pickup>
						<segment sequence="1">
							<address store_reference="'.$vars['storeReference'].'" />
							<time collect_before="'.$vars['collectBefore'].'" collect_after="'.$vars['collectAfter'].'" />
							<pickup_items>'.$pickupItems.'</pickup_items>
						</segment>
					</pickup>
					<delivery>
						<segment sequence="1">
							<address 
							 name="'.$vars['customerName'].'"
							 street1="'.$vars['deliveryStreet'].'"
							 city="'.$vars['deliveryCity'].'"
							 postal_code="'.$vars['deliveryPostcode'].'"
							 country="'.$vars['deliveryCountry'].'"
							/>
							<delivery_items>'.$deliveryItems.'</delivery_items>
							<contact name="'.$vars['customerName'].'" email="'.$vars['customerEmail'].'" phone="'.$vars['customerPhone'].'" />
							<time deliver_before="'.$vars['deliverBefore'].'" deliver_after="'.$vars['deliverAfter'].'" />
						</segment>
					</delivery>
				</quote>
			</ShutlGenerateQuoteRequest>';	
			Mage::log($template, null, 'shutl_templates.log');			
		return $template;	
	}
	

	public function getQuote(Mage_Shipping_Model_Rate_Request $request, $method) {

		Mage::getSingleton('core/session')->getMessages(true);

		$_helper = Mage::helper('shutl');
		$deliveryPostCode = $request['postcode'];
		$totalWeight = 0;
		$totalVolume = 0;
		$sku = array();

		$vehicle = '';
		foreach($request['all_items'] as $k => $v) {

			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $v['sku']);
			if (!$product->getPod1Shutl()) {
//				Mage::getSingleton('core/session')->addError('Shutl is not available for all items in your cart.');
				return false;
			}
			$sku[] = $v['sku'];
			$totalWeight += $v['weight'];
			if (is_numeric($product->getHeight()) && is_numeric($product->getLength()) && is_numeric($product->getWeight())) {

				$totalVolume += ($product->getHeight() * $product->getLength() * $product->getWidth());
			}
			if ($product->getPod1ShutlDefaultVehicle() > $vehicle) {
				$vehicle = $product->getPod1ShutlDefaultVehicle();
			}
			
		}
		
		if ($totalVolume > 0) {
			$vehicle = '';		
		} elseif ($vehicle == '') {
			$vehicle = Mage::helper('shutl')->getDefaultVehicle();				
		}

		$closestStore	= $this->findClosestStore($deliveryPostCode, $sku);

		if ($closestStore === false) {
			Mage::log('Cannot determine closest store.', null, 'shutl.log');
			Mage::getSingleton('core/session')->addError($_helper->getErrorMessage('0'));
			return false;
		} elseif ($closestStore === -1) {
			Mage::log('Delivery Address not in London.', null, 'shutl.log');		
			return false;
		}


		$storeReference	= $closestStore['reference'];		
		
		$timezone 		= $closestStore['timezone'];
		$timeStamp		= date('Y-m-d H:m:s');
		$timeStamp		= str_replace(' ', 'T', $timeStamp). $timezone;

		
		$clientId		= Mage::helper('shutl')->getClientId();

		$vars = array(
			'timestamp' 		=> $timeStamp,
			'clientId'			=> $clientId,
			'vehicle'			=> $vehicle,
			'totalWeight'		=> $totalWeight,
			'totalVolume'		=> $totalVolume,
			'storeReference'	=> $storeReference,
			'deliveryPostCode'	=> $deliveryPostCode,
			'collectBefore'		=> '',//$method['collect_before'],
			'collectAfter'		=> '',//$method['collect_after'],
			'deliverBefore'		=> ($method['deliver_before']) ? $method['deliver_before'] . $timezone : '',
			'deliverAfter'		=> ($method['deliver_after']) ? $method['deliver_after'] . $timezone : ''
		);
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$magentoId = $quote->getId() . '-' . time();
		Mage::log($magentoId, null, 'shutl_id.log');

		$shippingAddress 			= $quote->getShippingAddress();		
		$billingAddress 			= $quote->getBillingAddress();				
		$items 						= $quote->getAllitems();

		$vars['customerName']		= $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();
		$vars['customerEmail']		= ($billingAddress->getEmail()) ? $billingAddress->getEmail() : Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		$vars['customerPhone']		= $shippingAddress->getTelephone();
		
		$vars['deliveryStreet']		= implode(', ', $shippingAddress->getStreet());
		$vars['deliveryCity']		= $shippingAddress->getCity();
		$vars['deliveryPostcode']	= $shippingAddress->getPostcode();
		$vars['deliveryCountry']	= $shippingAddress->getCountryId();	
		
		$vars['items']				= array();
		$vars['total']				= 0;
		$vars['magento_id']			= $magentoId;
		
		foreach ($items as $item) {
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
			$myItem = array(
				'sku'		=> $item->getSku(),
				'name'		=> $item->getName(),
				'height'	=> $product->getHeight(),
				'length'	=> $product->getLength(),
				'width'		=> $product->getWidth(),
				'weight'	=> $item->getWeight(),
				'quantity'	=> (int)$item->getQty(),
				'price'		=> number_format($item->getPrice(),  2)
			);
			$vars['items'][] = $myItem;
			$vars['total'] += number_format($item->getPrice(),  2);
		}	
		$vars['total'] = number_format($vars['total'], 2);
		

		if (strpos(strtolower($_SERVER['REQUEST_URI']), 'onepage') !== false) {
			$xml = $this->_quoteXml($vars);				

			$response = Mage::helper('shutl')->query(
				Mage::helper('shutl')->getApiUrl() . 'quote'
				, $xml
				, array(
					'ClientID: ' . Mage::helper('shutl')->getClientId(),
					'APIKey: ' . Mage::helper('shutl')->getApiKey(),
					'Content-type: text/xml'
				), 'xml'
			);

		} else {
			$xml = $this->_quickQuoteXml($vars);		

			$response = Mage::helper('shutl')->query(
				Mage::helper('shutl')->getApiUrl() . 'quick_quote'
				, $xml
				, array(
					'ClientID: ' . Mage::helper('shutl')->getClientId(),
					'APIKey: ' . Mage::helper('shutl')->getApiKey(),
					'Content-type: text/xml'
				), 'xml'
			);
			
		}

		try {
			Mage::log($xml, null, 'shutl_requests.log');
			$xml = simplexml_load_string($response);
			Mage::log($response, null, 'shutl_responses.log');
			$attributes = $xml->attributes();
			if ($attributes['success'] == 0) {
				$error = $xml->error->attributes();
				$errorNumber = (string)$error->code;
				if ($errorNumber != '') {
					$errorMessages = array();

					foreach($xml->error as $errorNode) {
						$atts = $errorNode->attributes();
						$tmpError = $_helper->getErrorMessage($atts->code);
						if (!in_array($tmpError, $errorMessages)) {
							$errorMessages[] = $tmpError;
							Mage::getSingleton('core/session')->addError($tmpError);											
						}

						Mage::log($atts->code . ': ' . $atts->message, null, 'shutl.log');					
						

					}
				} else {
					Mage::log($error->message, null, 'shutl.log');									
					Mage::getSingleton('core/session')->addError($_helper->getErrorMessage($errorNumber));				
				}

				return false;
			}
			$quoteAttributes = $xml->quote->attributes();
			$deliveryTimes = $xml->quote->delivery->segment->time->attributes();
			
			$quoteInPounds = (String)$quoteAttributes['customer_quote_incl_vat'];	
			$baseCurrencyCode = Mage::app()->getBaseCurrencyCode();
			
			$deliverAfter = (String)$deliveryTimes['deliver_after'];			
			$deliverAfter = explode('T', $deliverAfter);
			$deliverDate = $deliverAfter[0];
			$deliverAfter = explode('+', str_replace('-', '+', $deliverAfter[1]));
			$deliverAfter = $deliverAfter[0];
			$deliverBefore = (String)$deliveryTimes['deliver_before'];
			$deliverBefore = explode('T', $deliverBefore);
			$deliverBefore = explode('+', str_replace('-', '+', $deliverBefore[1]));
			$deliverBefore = $deliverBefore[0];

			
			$return = array(
				'value'				=> '',
				'quote_id'			=> (String)$quoteAttributes['id'],
				'magento_id'		=> $magentoId,
				'deliver_date'		=> $deliverDate,
				'deliver_after' 	=> $deliverAfter,
				'deliver_before' 	=> $deliverBefore,				
			
			);
			if ($baseCurrencyCode == 'GBP') {
				$return['value'] = number_format($quoteInPounds, 4);
				return $return;			
			} else {
				$currencyRates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array('GBP'));				
				if (!is_array($currencyRates)) { return false; }
				if (sizeof($currencyRates) == 0) { return false; }				
				$toBase = (float)$currencyRates['GBP'];
				if ($toBase <= 0) { return false; }
				$quoteInBase = $quoteInPounds / $toBase;
				$return['value'] = number_format($quoteInBase, 4);
				return $return;
			}
			return false;

		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
			
	}


    public function getAllowedMethods() {
    	$return = array();
    	$services = explode(',', Mage::helper('shutl')->getServices());
    	$serviceOptions = Mage::getSingleton('shutl/source_service')->toOptionArray();

    	if (!$services) { return $return; }
    	if (is_array($services)) {
    		foreach ($services as $service) {
    			foreach($serviceOptions as $k => $v) {
    				if ($service == $v['value']) {
    					$return[] = $v;
    					break;
    				}
    			}
    		}
    	}
		Mage::log($services, null, 'shutl_methods.log');    	
    	Mage::log($return, null, 'shutl_methods.log');
        return array_reverse($return);
   
    }
    
    public function findClosestStore($deliveryPostCode, $sku) {
    	$closestStore = Mage::helper('shutl')->findClosestStore($deliveryPostCode, $sku);
		return $closestStore;
    }
    
}
