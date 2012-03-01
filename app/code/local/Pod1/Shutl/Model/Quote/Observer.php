<?php
class Pod1_Shutl_Model_Quote_Observer {


	public function __construct() {
	
	}

	public function save($observer) {
	
		$_helper = Mage::helper('shutl');

	    if (!$_helper->isActive()) { return; }
	    	
		$event = $observer->getEvent();
		$shippingDesc = $event->getOrder()->getShippingDescription();
		if (strpos(strtolower($shippingDesc), 'shutl') === false) { return; }

		$_helper  = Mage::helper('shutl');
		$quotes = unserialize(Mage::getSingleton('core/session')->getShutl());
		
		$sanatised = (strpos($shippingDesc, 'Shutl Later') === false) ? 'shutl_now' : 'shutl_later';
		if (is_array($quotes)) {
			$order = $event->getOrder();
			$order->setShippingDescription(strip_tags($order->getShippingDescription(), '<br><br />') . 'Shutl ref: ' . $quotes[$sanatised]['magento_id'] . '<br />');
			$order->save();
			$this->_placeOrder($quotes[$sanatised]['shutl_id'], $event->getOrder());
		}
		
		Mage::getSingleton('core/session')->setShutl();

	}



	protected function _placeOrder($quoteId, $order) {
		$_helper  = Mage::helper('shutl');
		$response = $_helper->query($_helper->getApiUrl() . 'order/' . $quoteId, null,  array(
					'ClientID: ' . Mage::helper('shutl')->getClientId(),
					'APIKey: ' . Mage::helper('shutl')->getApiKey(),
					'Content-type: text/xml'
				), null, true);


		try {
		
			$xml = simplexml_load_string($response);
			
			$attributes = $xml->attributes();
			
			if ($attributes['success'] == 0) {
				$error = $xml->error->attributes();
				$errorNumber = (string)$error->code;
				Mage::getSingleton('core/session')->addError($_helper->getErrorMessage($errorNumber));			
				return;
			}
			$orderAttributes = $xml->order->attributes();			
			
			$shipment = Mage::getModel('sales/order_shipment');
			$shipment->setOrder($order);
			
			$items = $order->getAllItems(); // get all order items
			foreach ($items as $item) {
				// populate shipment item object
				$shipmentItem = Mage::getModel('sales/order_shipment_item'); // load order shipment item3
				$shipmentItem->setShipment($shipment); // assign shipment to shipment item
				$shipmentItem->setOrderItem($item); // assign item to shipment item								
				$shipmentItem->setQty(trim(number_format($item->qty_ordered,0))); // assign qty
				$shipment->addItem($shipmentItem); // add shipment item to shipment
			}						
			
//            $track					= Mage::getModel('sales/order_shipment_track');;
//            $track->carrier_code	= Mage::helper('shutl')->getCode();
//            $track->title			= Mage::helper('shutl')->getTitle();
//            $track->number			= $orderAttributes['id'];
            
            
//            $shipment->addTrack($track);
			$shipment->getOrder()->setIsInProcess(true);
			$shipment->save();
			
		} catch (Exception $e) {
			print_r($e->getMessage());
		
		}
	}
}