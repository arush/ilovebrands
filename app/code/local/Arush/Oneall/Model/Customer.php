<?php

class Arush_Oneall_Model_Customer extends Mage_Customer_Model_Customer {

	/**
     * Validate password with salted hash
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
		// TODO Make a more secure method of bypassing password (PSE-27)
		if(Mage::getSingleton('oneall/session')->getLoginRequest()===true){
			Mage::getSingleton('oneall/session')->setLoginRequest(false);
			return true;
		}
        return parent::validatePassword($password);
    }

}