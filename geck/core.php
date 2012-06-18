<?php

header('Content-Type: text/plain');

ini_set('display_errors',true);
include('../app/Mage.php');
Mage::setIsDeveloperMode(true);

Mage::app(); //pass in store code if you like
date_default_timezone_set('Europe/London'); // add your locale - thanks to @benmarks from BlueAcorn for this
setlocale(LC_MONETARY, 'en_GB'); // change this to display different currencies


function getSoldCount($orders) {

	$count = 0;
	$number = count($orders);
	return $number;
}

function getOrders($from,$to) {
	$orders = Mage::getSingleton('sales/order')->getCollection()
		->addAttributeToSelect('*')
		->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to))
		// ->addFieldToFilter('status', 'complete')
	 	->addFieldToFilter('status', array('in' => array('complete','processing')));
	return $orders;
}

//excludes shipping amount
function getSoldValue($orders) {
	$totalSales = 0;
	foreach($orders as $order) {
		$totalSales = $totalSales + $order->getGrandTotal() - $order->getShippingAmount();
	}
	return $totalSales;
}


?>