<?php

require_once('core.php');

// t = time period from now to last monday midnight
// y = time period from last monday midnight to 7 days before that

// $ts = date('Y-m-d H:i:s', strtotime('last monday'));
$ts = date('Y-m-d H:i:s', strtotime('last monday'));
$te = date('Y-m-d H:i:s', mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y')));

$ys = date('Y-m-d H:i:s', strtotime('-1 week',strtotime('last monday')));
$ye = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));


if (isset($_POST) && isset($_SERVER['PHP_AUTH_USER'])) {


	/* Check API key */
    if ('1024' == $_SERVER['PHP_AUTH_USER']) {
		$sales1 = getOrders($ts,$te);
		$count1 = getSoldCount($sales1);
		$total1 = getSoldValue($sales1);

		$sales2 = getOrders($ys,$ye);
		$count2 = getSoldCount($sales2);
		$total2 = getSoldValue($sales2);

		$prefix = '<![CDATA[&pound;]]>';

		$currentSales = array("text"=>"Sales this week", "value"=>$total1, "prefix"=>$prefix);
		$previousSales = array("text"=>"on last week", "value"=>$total2, "prefix"=>$prefix);

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