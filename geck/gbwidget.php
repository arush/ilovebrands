<?php
/**
 * Include Response class and create object Response
 */
require_once('response.class.php');
$response_obj = new Response();

if (isset($_POST) && isset($_SERVER['PHP_AUTH_USER'])) {
    /**
     * Set response format
     */
    $format = isset($_POST['format']) ? (int)$_POST['format'] : 1;
    $format = ($format == 1) ? 'xml' : 'json';
    $response_obj->setFormat($format);

	/**
     * Metrics:
     * m - metric type - 1=order volume, 2=order value (default 1)
     */
	$metric = isset($_GET['m']) ? (int)$_GET['m'] : 1;
	
    /**
     * Dates type:
     * ps - period start (days ago incl.) - default 1 
     * pe - period end - default 1
     * pps - prior period start (for comparison purposes) - optional
     * ppe - prior period end - optional
	 * The default values will return sales yesterday.  To return sales in past 30 days (including today) and compare against previous 30 days use the following:
	 * ps=30&pe=1&pps=60&ppe=31
     */
	$ps = isset($_GET['ps']) ? (int)$_GET['ps'] : 0;
	$pe = isset($_GET['pe']) ? (int)$_GET['pe'] : 1;
	$pps = isset($_GET['pps']) ? (int)$_GET['pps'] : 0;
	$ppe = isset($_GET['ppe']) ? (int)$_GET['ppe'] : 0;
    
	/**
     * Work out dates from input params
     */
	$psdate = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m") , date("d") - $ps, date("Y")));
	$pedate = date('Y-m-d H:i:s', mktime(24, 0, 0, date("m") , date("d") - $pe, date("Y")));
	$ppsdate = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m") , date("d") - $pps, date("Y")));
	$ppedate = date('Y-m-d H:i:s', mktime(24, 0, 0, date("m") , date("d") - $ppe, date("Y")));

    /**
     * Get the number of orders
     *
     * @param $start, $finish, $metric
     * @return string
     */
    function getSales($start,$finish,$metric) {
		
		$response=0;
		//create soap object
		$proxy = new SoapClient('http://www.ilovebrandsoutlet.com/api/soap/?wsdl');

		// create authorized session id using api user name and api key
		// $sessionId = $proxy->login('apiUser', 'apiKey');
		$sessionId = $proxy->login('geckoboard', 'evogue2011');
		
		$filters = array(
		    'created_at' => array("from"=>$start, "to"=>$finish),
			
			 //'status' => array('nin'=>array())	
		     'status' => array('nin'=>array('On Hold','Canceled', 'Suspected Fraud', 'Payment Review'))	
		);
		$orders = $proxy->call($sessionId, 'sales_order.list', array($filters));
		
		if($metric==2) {
			foreach($orders as $order) {
				$response=$response+$order['grand_total'];
			}
		} 
		
		if($metric==1)  {
        	$response =$response+count($orders);
		}
		
		if($metric==3) {
		foreach($orders as $order) {
				$response= round($response+$order['grand_total']/count($orders),2);
				
			}
			}
			
		return $response;
    }
	
    /**
     * Check API key
     */
    //if ('evogue2011' == $_SERVER['PHP_AUTH_USER']) {
        switch($metric) {
            case 1:
                if($pps>0){
			$data = array('item' => array(
                                array('value' => getSales($psdate,$pedate,1), 'text' => 'Sales this period'),
                                array('value' => getSales($ppsdate,$ppedate,1), 'text' => 'Sales last period'),
                ));
			} else {
			$data = array('item' => array(
                            array('value' => getSales($psdate,$pedate,1), 'text' => 'Sales this period'),
                            array('value' => '', 'text' => ''),
            ));}
                $response = $response_obj->getResponse($data);
                break;
            case 2:
	           if($pps>0){
				$data = array('item' => array(
	                                array('value' => getSales($psdate,$pedate,2), 'text' => 'Sales this period'),
	                                array('value' => getSales($ppsdate,$ppedate,2), 'text' => 'Sales last period'),
	                ));
				} else {
				$data = array('item' => array(
	                            array('value' => getSales($psdate,$pedate,2), 'text' => 'Sales this period'),
	                            array('value' => '', 'text' => ''),
	            ));}
	                $response = $response_obj->getResponse($data);
	                break;
			case 3:
	           if($pps>0){
				$data = array('item' => array(
	                                array('value' => getSales($psdate,$pedate,3), 'text' => 'Sales this period'),
	                                array('value' => getSales($ppsdate,$ppedate,3), 'text' => 'Sales last period'),
	                ));
				} else {
				$data = array('item' => array(
	                            array('value' => getSales($psdate,$pedate,3), 'text' => 'Sales this period'),
	                            array('value' => '', 'text' => ''),
	            ));}
	                $response = $response_obj->getResponse($data);
	                break;
        }
        echo $response;
    // }
    // else {
    //     Header("HTTP/1.1 403 Access denied");
    //     $data = array('error' => 'Access denied.');
    //     echo $response_obj->getResponse($data);
    // }
}
else {
    //debug
    echo 

?>