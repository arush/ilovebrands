<?php

	$ds = DIRECTORY_SEPARATOR;

	$mageFilename = '.'. $ds .'app'. $ds .'Mage.php';

	if (!file_exists($mageFilename)) {
		echo $mageFilename." was not found";
		exit;
	}

	require_once $mageFilename;

    umask( 0 );

	if($_POST) {

		Mage::app("default")->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

		foreach(Mage::app()->getStores() as $store){
			if($store->getIsActive()){
				$defaultStoreId = $store->getStoreId();
				break;
			}
		}

		Mage::app("default")->setCurrentStore($defaultStoreId);
		Mage::helper('mailchimp')->webHookFilter($_POST);

	}else{
		echo "<div style='width:500px;padding-top:50px;margin:auto;'>" .
			 "<p>This file will capture the <b>MailChimp</b> updates.</p>" .
			 "<p>Thanks for using <b>Ebizmarts</b> modules</p>".
			 "<a href='http://www.ebizmarts.com' style='padding:10px;float:right;'>www.ebizmarts.com</a>" .
			 "</div>";
	}


	?>