<?php 
class Arush_Facebook_Helper_Data extends Mage_Core_Helper_Abstract{

	public function getFbPageId() {
		$id = Mage::getStoreConfig('facebook/options/fbpageid');
		
		return $id;
	}
	
	
	public function parsePageSignedRequest() {
	    $secret = '99d4debf0157da44e0609e6f305d756f';
	    
	    if (isset($_REQUEST['signed_request'])) {
	      $encoded_sig = null;
	      $payload = null;
	      list($encoded_sig, $payload) = explode('.', $_REQUEST['signed_request'], 2);
	      $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
	      $data = json_decode(base64_decode(strtr($payload, '-_', '+/'), true));
	      
	      $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	  
	  if ($sig !== $expected_sig) {
	    echo 'bad signed JSON signature!';
	    error_log('Bad Signed JSON signature!');
	    return null;
	  } else {  return $data;}
	  
	    }
	    return false;
	}

	public function fbApiCall($apiUrl) {
				
		$result = "rpxCallUrl: no result yet";

        try {
			
			
			/*
$OAdomain = $this->getOneallApiDomain();
			$OAusername = $this->getOneallPublicKey();
			$OApassword = $this->getOneallPrivateKey();
			
*/
			$curl = curl_init();
		        
		    curl_setopt($curl, CURLOPT_URL, $apiUrl);
		    curl_setopt($curl, CURLOPT_HEADER, 0);
/*  			curl_setopt($curl, CURLOPT_USERPWD, $OAusername . ":" . $OApassword); */
		    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
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

	public function isFan() {
		$isFan = false;
		if($signed_request = $this->parsePageSignedRequest()) {
		    if(isset($signed_request->page->liked)) {
		    	if($signed_request->page->liked == 1) {
		    		$isFan = true;
		    		return $isFan;
		    	}
		    	else {return $isFan;}
		    }
		    
		    else {
					// print_r($signed_request);
					//print_r($signed_request);
				}
		}
				
			else {echo 'something went really wrong';}

	}
	
	public function buildApiRequest($signed_request) {
		if(isset($signed_request->oauth_token)) {
		    	
		    	$auth_token = $signed_request->oauth_token;
		    	$user_id = $signed_request->user_id;
		    	$apiUrl = 'https://graph.facebook.com/' . $user_id . '/likes?access_token=' . $auth_token;
		    	$userLikes = $this->fbApiIsFan($apiUrl);
				$numLikes = count($userLikes->data);
				// echo $numLikes;				
				$i = 0;
				$fbPageId = $this->getFbPageId();

				while($isFan == false && $i < $numLikes) {
					if($userLikes->data[$i]->id == $fbPageId) {
						$isFan = true;
						return $isFan;
					}
					$i++;
				}
		    }
		 else {echo 'No auth token';}
	}

} 


 
?>