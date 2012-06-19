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

		$aov1 = round($sales1/$count1,2);
		$aov2 = round($sales2/$count2,2);

		$currentAov = array("text"=>"AOV today", "value"=>$aov1, "prefix"=>$prefix);
		$previousAov = array("text"=>"on yesterday", "value"=>$aov2);

		$response = array("item"=>array($currentAov,$previousAov));

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