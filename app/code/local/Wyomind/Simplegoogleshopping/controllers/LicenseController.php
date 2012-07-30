<?php

class Wyomind_Simplegoogleshopping_LicenseController extends Mage_Core_Controller_Front_Action
{
    public function activationAction(){
		foreach($_POST as $key=>$value)$$key=$value;
		
		$activation_key=Mage::getStoreConfig("simplegoogleshopping/license/activation_key");
		$base_url=Mage::getStoreConfig("web/secure/base_url");
		$registered_version=Mage::getStoreConfig("simplegoogleshopping/license/version");
	
		
		if(isset($wgs_activation_key) && $wgs_activation_key==Mage::getStoreConfig("simplegoogleshopping/license/activation_key")){
			if(isset($wgs_status)) {
				switch($wgs_status){
					case "success" : 
						Mage::getConfig()->saveConfig("simplegoogleshopping/license/version", $wgs_version, "default", "0");
						Mage::getConfig()->saveConfig("simplegoogleshopping/license/activation_code", $wgs_activation, "default", "0");
						Mage::getConfig()->cleanCache();
						Mage::getSingleton("core/session")->addSuccess(Mage::helper("simplegoogleshopping")->__($wgs_message));
					break;
					case "error" : 
						Mage::getSingleton("core/session")->addError(Mage::helper("simplegoogleshopping")->__($wgs_message));
						Mage::getConfig()->saveConfig("simplegoogleshopping/license/activation_code", "", "default", "0");
						Mage::getConfig()->cleanCache();
					break;
					case "uninstall" : 
						 Mage::getSingleton("core/session")->addError(Mage::helper("simplegoogleshopping")->__($wgs_message));
						 Mage::getConfig()->saveConfig("simplegoogleshopping/license/activation_key", "", "default", "0");
						 Mage::getConfig()->saveConfig("simplegoogleshopping/license/activation_code", "", "default", "0");
						Mage::getConfig()->cleanCache();
						 echo "
						<form action='http://www.wyomind.com/license_activation/?method=post' id='license_uninstall' method='post'>
							<input type='hidden' type='action' value='uninstall' name='action'>
							<input type='hidden' value='".$base_url."' name='domain'>
							<input type='hidden' value='".$activation_key."' name='activation_key'>
							<input type='hidden' value='".$registered_version."' name='registered_version'>
							<button type='submit'>If nothing happens click here !</button>
							<script language='javascript'>
								document.getElementById('license_uninstall').submit();
							</script>
						</form>
						 ";
						 die();
						 
					break;
					default :
						 Mage::getSingleton("core/session")->addError(Mage::helper("simplegoogleshopping")->__("An error occurs while retrieving license activation (500)"));
						 Mage::getConfig()->saveConfig("simplegoogleshopping/license/activation_code", "", "default", "0");
						 Mage::getConfig()->cleanCache();
					break;
					
				}
			} 
			else  {
				Mage::getSingleton("core/session")->addError(Mage::helper("simplegoogleshopping")->__("An error occurs while retrieving license activation (404)."));
				
			}
		}
		else  Mage::getSingleton("core/session")->addError(Mage::helper("simplegoogleshopping")->__("Invalid activation key."));
		
		$this->loadLayout();
		$this->renderLayout();
		
    }
}