<?php
header('Content-Type: text/plain');

ini_set('display_errors',true);
include('app/Mage.php');
Mage::setIsDeveloperMode(true);

Mage::app(); //pass in store code if you like
date_default_timezone_set('Europe/London'); // add your locale - thanks to @benmarks from BlueAcorn for this



function getSoldCount($orders) {

	$count = 0;
	$number = count($orders);
	return $number;
}

function getOrders($from,$to) {
	$orders = Mage::getSingleton('sales/order')->getCollection()
		->addAttributeToSelect('*')
		->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to))
		->addFieldToFilter('status', 'complete')
	    ->addFieldToFilter('status', 'processing');
}

//excludes shipping amount
function getSoldValue($orders) {
	$totalSales = 0;
	foreach($orders as $order) {
		$totalSales = $totalSales + $order->getGrandTotal() - $order->getShippingAmount();
	}
	return $totalSales;
}

// t = time period from now to last monday midnight
// y = time period from last monday midnight to 7 days before that

$ts = date('Y-m-d H:i:s', strtotime('last monday'));
$te = date('Y-m-d H:i:s', mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y')));

$ys = date('Y-m-d H:i:s', strtotime('-1 week',strtotime('last monday')));
$ye = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));


$sales1 = getOrders($ts,$te);
$count1 = getSoldCount($sales1);
$total1 = getSoldValue($sales1);

$sales2 = getOrders($ys,$ye);
$count2 = getSoldCount($sales2);
$total2 = getSoldValue($sales2);

echo "Sales value last week: " . $total1 . "<br/>Sales value week before that: " . $total2;
echo "Sales count last week: " . $count1 . "<br/>Sales value week before that: " . $count2;







