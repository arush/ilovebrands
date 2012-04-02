<?php
class Vdh_Randomquote_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template {

    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null) {
    
		$params = Mage::app()->getFrontController()->getRequest()->getParams();
		Mage::log($params, null, 'email.log');
		if (!array_key_exists('invite_style', $params) || !array_key_exists('invite_quote', $params)) {
	    	return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
		}
				
		$inviteStyle = Mage::getModel('randomquote/style')->load($params['invite_style']);
		$randomQuote = Mage::getModel('randomquote/quote')->load($params['invite_quote']);	
		$newVars = $vars;
		$newVars =  array_merge(
			$newVars,
			array("referral_url"=>Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId())->getCustomerurl()),
			$randomQuote->getData()
		);
		Mage::log($randomQuote, null, 'email.log');
		
    	return parent::sendTransactional($templateId, $sender, $email, $name, $newVars, $storeId);
    }
    	
	
}