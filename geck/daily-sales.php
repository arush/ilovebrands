<?php

require_once('core.php');


$ts = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d'), date('Y')));
$te = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')));

$ys = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
$ye = $ts;

if (isset($_POST) && isset($_SERVER['PHP_AUTH_USER'])) {


	/* Check API key */
    if ('1024' == $_SERVER['PHP_AUTH_USER']) {
		$sales1 = getOrders($ts,$te);
		$count1 = getSoldCount($sales1);
		$total1 = getSoldValue($sales1);

		$sales2 = getOrders($ys,$ye);
		$count2 = getSoldCount($sales2);
		$total2 = getSoldValue($sales2);

		$prefix = '£';

		$currentSales = array("text"=>"Sales today", "value"=>$total1, "prefix"=>$prefix);
		$previousSales = array("text"=>"on yesterday", "value"=>$total2);

		$response = array("item"=>array($currentSales,$previousSales));

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