<?php class Vdh_Randomquote_SocialController extends Mage_Core_Controller_Front_Action {
	
	public function facebookFriendsListAction() {

		$identifier = Mage::getModel('oneall/identifiers')
			->getCollection()
			->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId())
			->addFieldToFilter('provider', 'facebook')
			->getFirstItem();
			
			print_r($identifier->getData());



		$curl = curl_init();
	        
	    curl_setopt($curl, CURLOPT_URL, ' https://graph.facebook.com/psychocrackpot/friends');
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	    curl_setopt($curl, CURLOPT_VERBOSE, 0);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_FAILONERROR, 0);
		
	 	$response = curl_exec($curl);
	 	print_r($response);

	
	}

	public function facebookAction() {
		
		$identifier = Mage::getModel('oneall/identifiers')
			->getCollection()
			->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId())
			->addFieldToFilter('provider', 'facebook')
			->getFirstItem();
		
		if (!$identifier) {
			echo json_encode(array('message' => 'No facebook profile found.'));
			return;
		}
		
		$message = $this->getRequest()->getParam('message');
		
	
		$curl = curl_init();
	        
	    curl_setopt($curl, CURLOPT_URL, ' https://graph.facebook.com/'.$identifier->getIdentifier(). '/feed');
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	    curl_setopt($curl, CURLOPT_VERBOSE, 0);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_FAILONERROR, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		
		print_r('<xmp>[');
		print_r(Mage::getSingleton('core/session')->getFacebook());
		print_r(']</xmp>');		
//		curl_setopt($curl, CURLOPT_POSTFIELDS, array('access_token' => Mage::getSingleton('oneall/session')->getProfile())); 	    
	 
//	 	$response = curl_exec($curl);
		
//curl -F 'access_token=...' \
//     -F 'message=Hello, Arjun. I like this new API.' \
//     https://graph.facebook.com/arjun/feed
		
		
	}
}