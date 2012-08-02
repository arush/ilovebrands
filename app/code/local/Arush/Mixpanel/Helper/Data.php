<?php

class Arush_Mixpanel_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	*
	* Get the user's IP address and check against proxies
	* Author: @ldn_tech_exec
	* Based on code from http://thepcspy.com/read/getting_the_real_ip_of_your_users/
	**/
	public function getUserIp() {

        if (isset($_SERVER)) {

        	// if using a proxy
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
                return $_SERVER["HTTP_X_FORWARDED_FOR"];
            
            if (isset($_SERVER["HTTP_CLIENT_IP"]))
                return $_SERVER["HTTP_CLIENT_IP"];

            return $_SERVER["REMOTE_ADDR"];
        }

        if (getenv('HTTP_X_FORWARDED_FOR'))
            return getenv('HTTP_X_FORWARDED_FOR');

        if (getenv('HTTP_CLIENT_IP'))
            return getenv('HTTP_CLIENT_IP');

        return getenv('REMOTE_ADDR');
    }
}