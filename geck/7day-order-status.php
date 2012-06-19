<?php

require_once('core.php');

$ts = date('Y-m-d H:i:s', strtotime('this monday'));
$te = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')));

$ys = date('Y-m-d H:i:s', strtotime('-1 week',$ts));
$ye = $ts;


if (isset($_POST) && isset($_SERVER['PHP_AUTH_USER'])) {


	/* Check API key */
    if ('1024' == $_SERVER['PHP_AUTH_USER']) {
		$currentOrders = getOrders($ts,$te);
		
		$countAtProcessing = countAtStatus($currentOrders,'processing');
		$countAtComplete = countAtComplete($currentOrders,'complete');
		$countAtClosed = countAtComplete($currentOrders,'closed');

		$ordersRefunded = array("text"=>"Orders Refunded", "value"=>$countAtClosed);
		$ordersProcessing = array("text"=>"Orders Processing", "value"=>$countAtProcessing);
		$ordersComplete = array("text"=>"Orders Invoiced", "value"=>$countAtComplete);

		$response = array("item"=>array($ordersRefunded,$ordersProcessing,$ordersComplete));

		$json = json_encode($response);

		echo $json;        
    
    } else {
        Header("HTTP/1.1 403 Access denied");
        $data = array('error' => 'Nice try, asshole.');
        echo $data;
    }

} else {
	Header("HTTP/1.1 404 Page not found");
}



?>