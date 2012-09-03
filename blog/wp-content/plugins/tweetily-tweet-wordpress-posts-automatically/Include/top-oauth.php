<?php

if (!class_exists('WP_Http')) {
    include_once( ABSPATH . WPINC . '/class-http.php' );
}

require_once( 'top-debug.php' );

define('TOP_OAUTH_CONSUMER_KEY', 'x1f7w1y3gu8WFMgWramMQ');


define('TOP_OAUTH_REQUEST_URL', 'http://api.twitter.com/oauth/request_token');
define('TOP_OAUTH_ACCESS_URL', 'http://api.twitter.com/oauth/access_token');
define('TOP_OAUTH_AUTHORIZE_URL', 'http://api.twitter.com/oauth/authorize');
define('TOP_OAUTH_REALM', 'http://twitter.com/');

class TOPOAuth {

    var $duplicate_tweet;
    var $can_use_curl;
    var $response_code;
    var $oauth_time_offset;
    var $error_message;
    var $oauth_consumer_key;
    var $oauth_consumer_secret;

    function TOPOAuth() {
        $this->duplicate_tweet = false;
        $this->can_use_curl = true;
        $this->response_code = false;
        $this->error_message = false;
        $this->oauth_time_offset = 0;
        $this->set_defeault_oauth_tokens();
    }

    function set_defeault_oauth_tokens() {
        $this->oauth_consumer_key = TOP_OAUTH_CONSUMER_KEY;
        $this->oauth_consumer_secret = "oD2YnV4fdCrVV3Nrg05nu4eoKFqCLOQAZTjywWQJuxg";

    }

    function set_oauth_tokens($key, $secret) {
        $this->oauth_consumer_key = $key;
        $this->oauth_consumer_secret = $secret;
    }

    function get_response_code() {
        return $this->response_code;
    }

    function get_error_message() {
        return $this->error_message;
    }

    function enable_curl($value) {
        $this->can_use_curl = $value;
    }

    function encode($string) {
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($string)));
    }

    function create_signature_base_string($get_method, $base_url, $params) {
        if ($get_method) {
            $base_string = "GET&";
        } else {
            $base_string = "POST&";
        }

        $base_string .= $this->encode($base_url) . "&";

        // Sort the parameters
        ksort($params);

        $encoded_params = array();
        foreach ($params as $key => $value) {
            $encoded_params[] = $this->encode($key) . '=' . $this->encode($value);
        }

        $base_string = $base_string . $this->encode(implode($encoded_params, "&"));

        TOP_DEBUG('Signature base string is: ' . $base_string);
        return $base_string;
    }

    function params_to_query_string($params) {
        $query_string = array();
        foreach ($params as $key => $value) {
            $query_string[$key] = $key . '=' . $value;
        }

        ksort($query_string);

        return implode('&', $query_string);
    }

    function do_get_request($url) {
        $request = new WP_Http;
        $result = $request->request($url);
        $this->response_code = $result['response']['code'];
        TOP_DEBUG('do get request returned status code of ' . $this->response_code . ' for url - ' . $url);
        if ($result['response']['code'] == '200') {
            return $result['body'];
        } else {
            return false;
        }
    }

    function check_rate_limit()
    {
       
    }
    
    function do_request($url, $oauth_header, $body_params = '') {
        TOP_DEBUG('Doing POST request, OAUTH header is ' . $oauth_header);

        if (function_exists('curl_init') && $this->can_use_curl) {
        
          
            
            $ch = curl_init($url);

            TOP_DEBUG('..using CURL transport');

            // we're doing a POST request
            curl_setopt($ch, CURLOPT_POST, 1);

            $body_array = array();
            foreach ($body_params as $key => $value) {
                $body_array[] = urlencode($key) . '=' . urlencode($value);
            }

            if (top_is_debug_enabled()) {
                $param_str = print_r($body_array, true);
                TOP_DEBUG('..POST parameters are ' . $param_str);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, implode($body_array, '&'));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . $oauth_header));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // Don't use this option in safe mode
            if (!ini_get('safe_mode')) {
                @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

                TOP_DEBUG('..CURLOPT_FOLLOWLOCATION is ON');
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if (( $contents = curl_exec($ch))) {
                $response = curl_getinfo($ch);
                curl_close($ch);

                TOP_DEBUG('..CURL returned a status code of ' . $response['http_code']);

                $this->response_code = $response['http_code'];
                if ($response['http_code'] == 200) {
                    return $contents;
                } else {
                    TOP_DEBUG('..RESPONSE was ' . print_r($response, true));

                    switch ($response['http_code']) {
                        case 403:
                            $this->duplicate_tweet = true;
                            break;
                    }

                    $error_message_found = preg_match('#<error>(.*)</error>#i', $contents, $matches);
                    if ($error_message_found) {
                        $this->error_message = $matches[1];
                    }
                }
            } else {
                TOP_DEBUG("..CURL didn't return any contents");
                curl_close($ch);
            }
        } else {
            $request = new WP_Http;
            TOP_DEBUG('..using WP transport');
            $params = array();
            if ($body_params) {
                foreach ($body_params as $key => $value) {
                    $body_params[$key] = ( $value );
                }

                $params['body'] = $body_params;
            }

            $params['method'] = 'POST';
            $params['headers'] = array('Authorization' => $oauth_header);

            $result = $request->request($url, $params);

            if (!is_wp_error($result)) {

                TOP_DEBUG('..WP transport returned a status code of ' . $result['response']['code']);

                $this->response_code = $result['response']['code'];

                if ($result['response']['code'] == '200') {
                    return $result['body'];
                } else {
                    TOP_DEBUG('..RESPONSE was ' . print_r($result['response'], true));

                    switch ($result['response']['code']) {
                        case 403:
                            $this->duplicate_tweet = true;
                            break;
                    }

                    $error_message_found = preg_match('#<error>(.*)</error>#i', $result['body'], $matches);
                    if ($error_message_found) {
                        $this->error_message = $matches[1];
                    }
                }
            } else {
                TOP_DEBUG("..WP transport returned an error, " . $result->get_error_message());
            }
        }
        return false;
    }

    function get_nonce() {
        return md5(mt_rand() + mt_rand());
    }

    function parse_params($string_params) {
        $good_params = array();

        $params = explode('&', $string_params);
        foreach ($params as $param) {
            $keyvalue = explode('=', $param);
            $good_params[$keyvalue[0]] = $keyvalue[1];
        }

        return $good_params;
    }

    function set_oauth_time_offset( $offset ) {
		$this->oauth_time_offset = $offset;
	}
    
    function hmac_sha1($key, $data) {
        if (function_exists('hash_hmac')) {
            $hash = hash_hmac('sha1', $data, $key, true);

            return $hash;
        } else {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize) {
                $key = pack('H*', $hashfunc($key));
            }

            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hash = pack('H*', $hashfunc(( $key ^ $opad ) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));

            return $hash;
        }
    }

    function do_oauth($url, $params, $token_secret = '') {
        $sig_string = $this->create_signature_base_string(false, $url, $params);

        //$hash = hash_hmac( 'sha1', $sig_string, TOP_OAUTH_CONSUMER_SECRET . '&' . $token_secret, true );
        $hash = $this->hmac_sha1($this->oauth_consumer_secret . '&' . $token_secret, $sig_string);

        $sig = base64_encode($hash);

        $params['oauth_signature'] = $sig;

        $header = "OAuth ";
        $all_params = array();
        $other_params = array();
        foreach ($params as $key => $value) {
            if (strpos($key, 'oauth_') !== false) {
                $all_params[] = $key . '="' . $this->encode($value) . '"';
            } else {
                $other_params[$key] = $value;
            }
        }

        $header .= implode($all_params, ", ");

        return $this->do_request($url, $header, $other_params);
    }

    function get_request_token() {
        $params = array();
        
        $admin_url = get_option('top_opt_admin_url');
        if (!isset($admin_url)) {
            $admin_url = top_currentPageURL();
        }
        
        TOP_DEBUG( 'In function get_request_token' );
        $params['oauth_consumer_key'] = TOP_OAUTH_CONSUMER_KEY;
        //$params['oauth_callback'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&TOP_oauth=1';
        $params['oauth_callback'] = htmlentities($admin_url . '&TOP_oauth=1');
        
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_timestamp'] = time();
        $params['oauth_nonce'] = $this->get_nonce();
        $params['oauth_version'] = '1.0';

        if ( top_is_debug_enabled() ) {
			TOP_DEBUG( '..params are ' . print_r( $params, true ) );	
		}
        
        $result = $this->do_oauth(TOP_OAUTH_REQUEST_URL, $params);

        if ($result) {
            $new_params = $this->parse_params($result);
            return $new_params;
        }
    }

    function get_access_token($token, $token_secret, $verifier) {
        $params = array();

        TOP_DEBUG( 'In function get_access_token' );
        
        $params['oauth_consumer_key'] = $this->oauth_consumer_key;
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_timestamp'] = time() + $this->oauth_time_offset;
        $params['oauth_nonce'] = $this->get_nonce();
        $params['oauth_version'] = '1.0';
        $params['oauth_token'] = $token;
        $params['oauth_verifier'] = $verifier;

        if ( top_is_debug_enabled() ) {
			TOP_DEBUG( '..params are ' . print_r( $params, true ) );	
		}	
        
        $result = $this->do_oauth(TOP_OAUTH_ACCESS_URL, $params, $token_secret);
        if ($result) {
            $new_params = $this->parse_params($result);
            return $new_params;
        }
    }

    function update_status($token, $token_secret, $status) {
        $params = array();

        $params['oauth_consumer_key'] =$this->oauth_consumer_key;
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_timestamp'] = time() + $this->oauth_time_offset;
        $params['oauth_nonce'] = $this->get_nonce();
        $params['oauth_version'] = '1.0';
        $params['oauth_token'] = $token;
        $params['status'] = $status;

        if ( top_is_debug_enabled() ) {
			TOP_DEBUG( '..params are ' . print_r( $params, true ) );	
		}
        
        $url = 'http://api.twitter.com/1/statuses/update.xml';

        $result = $this->do_oauth($url, $params, $token_secret);
        if ($result) {
            $new_params = TOP_parsexml($result);
            return true;
        } else {
            return false;
        }
    }

    function was_duplicate_tweet() {
        return $this->duplicate_tweet;
    }

    function get_auth_url($token) {
        return TOP_OAUTH_AUTHORIZE_URL . '?oauth_token=' . $token;
    }

    function get_user_info($user_id) {
        $url = 'http://api.twitter.com/1/users/show.xml?id=' . $user_id;

        $result = $this->do_get_request($url);
        if ($result) {
            $new_params = TOP_parsexml($result);
            return $new_params;
        }
    }

}

?>
