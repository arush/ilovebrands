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
 * Shutl Quote front controller
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
 
class Pod1_Shutl_QuoteController extends Mage_Core_Controller_Front_Action {


	public function timeAction() {
		
		$_helper = Mage::helper('shutl');

		$sku = array();

		$id = urldecode($this->getRequest()->getParam('id'));
		if ($id != '') {
			$ids = explode(',', $id);		
			$products = Mage::getModel('catalog/product')->getCollection();
			$products->addAttributeToFilter('entity_id', array('in' => $ids));
			foreach($products as $product) {
				$sku[] = $product->getSku();
			}
		} else {
			$items = Mage::helper('checkout/cart')->getCart()->getItems();
			
			foreach ($items as $item) {
				if ($item->getProductType() == 'simple') {
					$sku[] = $item->getSku();				
				}

			}
		}

		if ($this->getRequest()->getParam('postcode')) {
			$postcode = urldecode($this->getRequest()->getParam('postcode'));		
		} else {
			$postcode =  Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getPostcode();
		}
		if (strtolower($postcode) == 'enter postcode here') { print_r('0'); return; }
		
		$storeReference = $_helper->findClosestStore($postcode, $sku);
		if (!$storeReference) { Mage::log('Could not locate closest store.', null, 'shutl.log'); print_r('0'); return; }
		
		$date		= $this->getRequest()->getParam('date');
		$separator	= $_helper->findDateSeparator($date);
		$date		= explode($separator, $_helper->convertDate($date));
		$selectedDay = date('l', mktime(0,0,0, $date[1], $date[2], $date[0]));
		
		$store = $_helper->getStoreInfo($storeReference['reference']);
		
		if (!$store) { Mage::log('Could not retrieve store details.', null, 'shutl.log'); print_r('0'); return; }
		
		try {

			$times = array();

			$timeAttributes = $store->time->attributes();
			$delayTime 		= (int)$timeAttributes['delay_time'];
			$timezone 		= new DateTimeZone((string)$timeAttributes['timezone']);	

			$timezoneDiff	= $timezone->getOffset(new DateTime("now", $timezone));
			
			$quoteTimeout = $_helper->getQuoteTimeout();
			$carrierSla = $_helper->getCarrierSla();
			if (!$carrierSla) { Mage::log('Could not retrieve Carrier SLA.', null, 'shutl.log'); print_r('0'); return; }
			$soonestTime = (
				$quoteTimeout
				 + (
				 	$delayTime > $carrierSla['pickup_sla'] ? $delayTime : $carrierSla['pickup_sla']
				 )
				  + $carrierSla['delivery_sla']
				  + 59
			) * 60;

			
			foreach ($store->opening_hours->day as $day) {
				$attributes = $day->attributes();

				if (strtolower((string)$attributes['type']) == strtolower($selectedDay)) {

					if (strtolower((string)$attributes['is_open']) == 'n') {
						print_r('0'); break;
					}
					$openingTime = explode(':', (string)$attributes['opening_time']);
					$openingTime = date('H:00', mktime($openingTime[0], $openingTime[1], 0, $date[1], $date[2], $date[0]) + $soonestTime);
					
					
					$currentTime = explode(':', date('H:m'));
					$currentTime = date('H:00', mktime($currentTime[0], $currentTime[1], 0, $date[1], $date[2], $date[0]) + $soonestTime + $timezoneDiff 
);

					if ($openingTime < $currentTime && date('Y-m-d') == implode('-', $date)) { $openingTime = $currentTime; }
					
					$openingTime = explode(':', $openingTime);
					$openingTime = (int)$openingTime[0];
					
					$closingTime = explode(':', (string)$attributes['closing_time']);
					
					$closingTime = date(
						'H:00', mktime(
							$closingTime[0], $closingTime[1], 0, 
							$date[1], $date[2], $date[0]
						)
						 - (15 * 60) //Standard Delay
						 + ($carrierSla['delivery_sla'] * 60) //Carrier Sla
						 + 59
						)
					 ;				 
					$closingTime = explode(':', $closingTime);
					$closingTime = (int)$closingTime[0];
					if ($closingTime == 0) { $closingTime = 24; }					
		
					if ($closingTime <= $openingTime) { Mage::log('Closing time less than opening time, store configuration issue.', null, 'shutl.log'); print_r('0'); break; }
			        $this->loadLayout();	
					$this->getLayout()->getBlock('root')->setData(
						array(
							'opening_time' => $openingTime, 
							'closing_time' => $closingTime,
							'selected_time'	=> $_helper->getSelectedTime()
						)
					);
			        $this->renderLayout();						
			        break;
			        
				}
			}
		} catch (Exception $e) {
			Mage::logException($e);
			print_r('0');
		}
		
	}

	public function productAction() {
		$_helper = Mage::helper('shutl');
		$product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));

		if(!$product) { return; }
		
		$carrier = Mage::getModel('shutl/carrier_shipping');
		$request = Mage::getModel('shipping/rate_request');
		$request->addData(
			array(
				'postcode' => $this->getRequest()->getParam('postcode'),
				'method' => $this->getRequest()->getParam('method'),
				'date' => $_helper->convertDate((string) $this->getRequest()->getParam('date')),
				'time' => $this->getRequest()->getParam('time'),
				'all_items' => array(
					$product->getSku() => array(
						'sku'		=> $product->getSku(),
						'width'		=> $product->getWidth(),
						'height'	=> $product->getHeight(),
						'length'	=> $product->getLength(),
						'weight'	=> $product->getWeight()			
					)
				)
			)
		);
        $this->loadLayout();
		
		$this->getLayout()->getBlock('root')->setData(
			array(
				'rates' => $carrier->collectRates($request),
			)
		);        	

        $this->renderLayout();
		
	}
	
	public function formAction() {

		$_helper					= Mage::helper('shutl');
		$today						= date('Y-m-d-M-j-N');
		$todayParts					= explode('-', $today);
		$daysAllowed				= $_helper->getDeliveryWindow();
		$startDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' -'.($todayParts[5]-1).' day'));
	
		$lastSelectableDate			= date('Y-m-d-M-j-N', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$lastSelectableDateParts	= explode('-', $lastSelectableDate);
	
		$endDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$endDate					= date('Y-m-d-M-j-N', strtotime($endDate . ' +'.(7-($lastSelectableDateParts[5])).' day'));						

        $this->loadLayout();
		$this->getLayout()->getBlock('root')->setData(
			array(
				'today'			=> $today,
				'start_date'	=> $startDate,
				'end_date'		=> $endDate,
				'last_selectable_date' => $lastSelectableDate,
				'current_date'	=> date('Y-m-d-M-j-N', strtotime($startDate)),
				'selected_date'	=> $_helper->getSelectedDate()
			)

		);        	
        $this->renderLayout();	
	}
}