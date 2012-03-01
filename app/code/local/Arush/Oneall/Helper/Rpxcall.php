<?php

class Arush_Oneall_Helper_Rpxcall extends Mage_Core_Helper_Abstract {

	public function getOneallApiKey() {
		return Mage::getStoreConfig('oneall/options/apikey');
	}
	
	public function getOneallApiDomain() {
		$prefix = 'https://';
		$domain = Mage::getStoreConfig('oneall/options/apidomain');
		$suffix = '.api.oneall.com/';
		
		$apidomain = $prefix.$domain.$suffix;
		
		return $apidomain;
	}
	
	public function getOneallPrivateKey() {
		return Mage::getStoreConfig('oneall/options/privatekey');
	}
	
	public function getOneallPublicKey() {
		return Mage::getStoreConfig('oneall/options/publickey');
	}
	

	public function rpxLookupSave() {
		/*
try {
			$lookup_rp = $this->rpxLookupRpCall();

			Mage::getModel('core/config')
				->saveConfig('oneall/vars/realm', $lookup_rp->realm)
				->saveConfig('oneall/vars/realmscheme', $lookup_rp->realmScheme)
				->saveConfig('oneall/vars/appid', $lookup_rp->appId)
				->saveConfig('oneall/vars/adminurl', $lookup_rp->adminUrl)
				->saveConfig('oneall/vars/socialpub', $lookup_rp->socialPub)
				->saveConfig('oneall/vars/enabled_providers', $lookup_rp->signinProviders)
				->saveConfig('oneall/vars/apikey', Mage::getStoreConfig('oneall/options/apikey'));
			Mage::getConfig()->reinit();

			return true;

		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addWarning('Could not retrieve account info. Please try again');
		}
		
*/
		return false;
	}

	/* replace this with Oneall api custom built call */
   /*
 public function rpxLookupRpCall() {
        $version = Mage::getConfig()->getModuleConfig("Arush_Oneall")->version;

        $postParams = array();
        $postParams["apiKey"] = $this->getOneallApiDomain();
        $postParams["pluginName"] = "magento";
        $postParams["pluginVersion"] = $version;

        $result = "rpxLookupRpCall: no result";
        try {
            $result = $this->rpxPost("lookup_rp", $postParams);
        }
        catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;

    }
*/
    public function rpxAuthInfoCall($token) {

        $postParams = array();

        $postParams["connection_token"] = $token;
        // $postParams["apiKey"] = $this->getOneallApiKey();
		$postParams["apidomain"] = $this->getOneallApiDomain();
		$postParams["username"] = $this->getOneallPrivateKey();
		$postParams["password"] = $this->getOneallPublicKey();

        $result = "rpxAuthInfoCall: no result";
        try {
            $result = $this->rpxPost("auth_info", $postParams);
        }
        catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;

    }



    public function rpxActivityCall($identifier, $activity_message, $url) {

        $postParams = array();
		$activity = new stdClass();

		$activity->action = $activity_message;
		$activity->url = $url;

		$activity_json = json_encode($activity);

        $postParams["activity"] = $activity_json;
        $postParams["identifier"] = $identifier;
        $postParams["apiKey"] = $this->getOneallApiKey();

        $result = "rpxActivityCall: no result";
        try {
            $result = $this->rpxPost("activity", $postParams);
        }
        catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;

    }


    private function rpxPost($method, $postParams) {

        /* only use this if Oneall support account activity and lookups in addition to auth 
        
        $rpxbase = "https://rpxnow.com";

        if ($method == "auth_info") {
            $method_fragment = "api/v2/auth_info";
        }
        elseif ($method == "activity") {
            $method_fragment = "api/v2/activity";
        }
        elseif ($method == "lookup_rp") {
            $method_fragment = "plugin/lookup_rp";
        }
        else {
            throw Mage::exception('Mage_Core', "method [$method] not understood");
        }
		
		$url = "$rpxbase/$method_fragment";
		*/
		$url = $postParams["apidomain"];
		$method = 'POST';
        $postParams["format"] = 'json';

        return $this->rpxCall($url, $method, $postParams);
		
    }

    private function rpxCall($url, $method='GET', $postParams=null) {

        $result = "rpxCallUrl: no result yet";

        try {
			
			
			$OAdomain = $this->getOneallApiDomain();
			$OAusername = $this->getOneallPublicKey();
			$OApassword = $this->getOneallPrivateKey();
			
			$curl = curl_init();
		        
		    curl_setopt($curl, CURLOPT_URL, $OAdomain.'connections/'.$postParams["connection_token"].'.json');
		    curl_setopt($curl, CURLOPT_HEADER, 0);
 			curl_setopt($curl, CURLOPT_USERPWD, $OAusername . ":" . $OApassword);
		    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		    curl_setopt($curl, CURLOPT_VERBOSE, 0);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		    curl_setopt($curl, CURLOPT_FAILONERROR, 0);
		 
		 	$json = curl_exec($curl);
		 	$info = curl_getinfo($curl);  
  
			$infoverbose = 'Took ' . $info['total_time'] . ' seconds for url ' . $info['url'] . ' with code ' . $info['http_code'];
		    
		    //Error
		    if ($json === false)
		    {
		        echo 'Curl error: ' . curl_error($curl);
		    }
		    //Success
		    else
		    {
		        //Close connection
		        curl_close($curl);		
	
	            try {
	               // if you're debugging, this is where you can see the json string
	               //print_r($json);
	               $result = json_decode($json);  
	            }
	            catch (Exception $e) {
	                throw Mage::exception('Mage_Core', $e);
	            }
	
	            if ($result) {
	                return $result;
	            }
	            else {
	                throw Mage::exception('Mage_Core', $infoverbose);
	            }
            
            }

        }
        catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

    }


	public function getFirstName($auth_info) {
		if (isset($auth_info->response->result->data->user->identity->name->givenName))
			return $auth_info->response->result->data->user->identity->name->givenName;
        
        if (!isset($auth_info->response->result->data->user->identity->name->formatted))
            return '';

		$name = str_replace(",", "", $auth_info->response->result->data->user->identity->name->formatted);

        if (!$name)
            return '';
        
        $split = explode(" ", $name);

		$fName = isset($split[0]) ? $split[0] : '';
		return $fName;
	}

	public function getLastName($auth_info) {
		if (isset($auth_info->response->result->data->user->identity->name->familyName))
			return $auth_info->response->result->data->user->identity->name->familyName;
        
        if (!isset($auth_info->response->result->data->user->identity->name->formatted))
            return '';

		$name = str_replace(",", "", $auth_info->response->result->data->user->identity->name->formatted);
        
        if (!$name)
            return '';
        
		$split = explode(" ", $name);
		$key = sizeof($split) - 1;

		$lName = isset($split[$key]) ? $split[$key] : '';
		return $lName;
	}
	
	public function getGender($auth_info) {
		if (isset($auth_info->response->result->data->user->identity->gender)) {
			$gender = $auth_info->response->result->data->user->identity->gender;
			
			//standardise
			$gender = strtolower($gender);
			if($gender === 'male') {
				$gender = 1;
			}
			else if($gender === 'female') {
				$gender === 2;			
			}
			else {
				// if any other gender is important to you, you're a bigger man than I. Insert attribute admin value here
				$gender = '';
			}			
			return $gender;

		}
        
        if (!isset($auth_info->response->result->data->user->identity->gender))
            $gender = '';
		
		
		
	}

}
?>