<?php

class Arush_Oneall_Helper_Data extends Mage_Core_Helper_Abstract {

	private $providers = array(
		'facebook'		=> 'facebook',
		'google'		=> 'google',
		'LinkedIn'		=> 'linkedin',
		'MySpace'		=> 'myspace',
		'twitter'		=> 'twitter',
		'Windows Live'	=> 'live_id',
		'Yahoo!'		=> 'yahoo',
		'AOL'			=> 'aol',
		'Blogger'		=> 'blogger',
		'Flickr'		=> 'flickr',
		'Hyves'			=> 'hyves',
		'Livejournal'	=> 'livejournal',
		'MyOpenID'		=> 'myopenid',
		'Netlog'		=> 'netlog',
		'OpenID'		=> 'openid',
		'Verisign'		=> 'verisign',
		'Wordpress'		=> 'wordpress',
		'PayPal'		=> 'paypal'
	);

	/**
	 * Returns whether the Enabled config variable is set to true
	 *
	 * @return bool
	 */
	public function isOneallEnabled() {
		if(Mage::getStoreConfig('oneall/options/enable')=='1' && strlen(Mage::getStoreConfig('oneall/options/apikey')) > 1)
			return true;

		return false;
	}

	/**
	 * Returns random alphanumber string
	 *
	 * @param int $length
	 * @param string $chars
	 * @return string
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		$chars_length = (strlen($chars) - 1);

		$string = $chars{rand(0, $chars_length)};

		for ($i = 1; $i < $length; $i = strlen($string)) {
			$r = $chars{rand(0, $chars_length)};

			if ($r != $string{$i - 1})
				$string .= $r;
		}

		return $string;
	}

	/**
	 * Returns the url of skin directory containing scripts and styles
	 *
	 * @return string
	 */
	public function _baseSkin() {
		return Mage::getBaseUrl('skin') . "frontend/arush";
	}

	/**
	 * Returns the full Auth URL for the Oneall Sign In Widget
	 *
	 * @return string
	 */
	public function getRpxAuthUrl($add=false) {
		$action = $add ? 'oneall/rpx/token_url_add' : 'oneall/rpx/token_url';
		$rpx_callback = urlencode(Mage::getUrl($action));
		$link = (Mage::getStoreConfig('oneall/vars/realmscheme') == 'https') ? 'https' : 'http';
		$link.= '://' . Mage::getStoreConfig('oneall/vars/realm');
		$link.= '/openid/v2/signin?token_url=' . $rpx_callback;

		return $link;
	}

	/** - not sure what this is needed for
	 * Returns an unserialized array of available providers
	 * or Null if empty (Invalid or missing API Key)
	 *
	 * @return string
	 */
	public function getRpxProviders() {
		$providers = Mage::getStoreConfig('oneall/vars/enabled_providers');
		if($providers)
			return explode(",", $providers);
		else
			return false;
	}

	public function buildProfile($response) {
		$provider = false;
		$provider = $response->response->result->data->user->identity->provider;
		if($provider == 'twitter') {
			$profile_name = $response->response->result->data->user->identity->preferredUsername;
		}
		else {$profile_name = $provider;}
		
		Mage::getSingleton('core/session')->setData(array($provider => $response->response->result->data->user->user_token));
		return array(
			'provider' => $provider,
			'identifier' => $this->getSocialId($response),
			'profile_name' => $profile_name,
			'user_token' => $response->response->result->data->user->user_token,
			'identity_token' => $response->response->result->data->user->identity->identity_token
		);
	}
	
	public function getSocialId($returnObject) {
		if(isset($returnObject->response->result->data->user->identity->accounts[0]->userid)) {
	    	return $returnObject->response->result->data->user->identity->accounts[0]->userid;
	    	}
	    	//currently only yahoo:
	    	else if(isset($returnObject->response->result->data->user->identity->id)) {
	    		return $returnObject->response->result->data->user->identity->id;
	    	}
	}
	
	public function getIdentityToken($response) {
		if(isset($response->response->result->data->user->identity->identity_token)) {
	    	return $response->response->result->data->user->identity->identity_token;
	    	}
		else { return "no identity token";}
	}
	
	public function getUserToken($response) {
		if(isset($response->response->result->data->user->user_token)) {
	    	return $response->response->result->data->user->user_token;
	    	}
		else { return "no user token";}
	}
	
	
	public function getDeleteTrue($returnObject) {
		if(isset($returnObject->response->result->status->code)) {
			if($returnObject->response->result->status->code === 200) {
				if($returnObject->response->result->data->plugin->data->action == "unlink_identity" &&
					$returnObject->response->result->data->plugin->data->status == "success") {
						$deleteTrue = true;
						return $deleteTrue;
				}
				else if($returnObject->response->result->data->plugin->data->action == "link_identity" &&
					$returnObject->response->result->data->plugin->data->status == "success") {
						$deleteTrue = false;
						return $deleteTrue;
				}
			}
		}
		else { 
		
			$deleteTrue = "Something went wrong, please contact customer support";
			return $deleteTrue;
		}
	}
	
	public function getThumbnailUrl() {
				
	}

}
































