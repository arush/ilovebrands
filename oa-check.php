<?php

if ( ! empty ($_GET['connection_token']))
{
  $oa_subdomain = 'brandid';
	$oa_application_public_key = 'a04a298d-11eb-4fd5-8f5d-86a88315408a';
	$oa_application_private_key = '549ba351-f916-4831-8aa4-57a47a439803';
	
	$oa_application_domain = $oa_subdomain.'.api.oneall.com';	

	$token = $_GET['connection_token'];

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://'.$oa_application_domain.'/connections/'.$token .'.json');

	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_USERPWD, $oa_application_public_key . ":" . $oa_application_private_key);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl, CURLOPT_FAILONERROR, 0);

	//Error
	if ( ($json = curl_exec($curl)) === false)
	{
		echo 'Curl error: ' . curl_error($curl);
	}
	//Success
	else
	{
		//Close connection
		curl_close($curl);
		echo $json;
	}

}

?>