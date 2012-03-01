<?php

class Ebizmarts_SagePayReporting_Block_Adminhtml_Sagepayreporting_Threedstatus extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sagepayreporting/threedstatus.phtml');
    }

	public function getAccessModel()
	{
		return Mage::getModel('sagepayreporting/sagepayreporting');
	}

    public function get3dStatus()
    {
    	try{
    		return $this->getAccessModel()->get3dSecureStatus();
    	}catch(Exception $e){
			return $e->getMessage();
    	}
    }

    public function get3dRules()
    {
		try{
    		return $this->getAccessModel()->get3dSecureRules();
    	}catch(Exception $e){
			return $e->getMessage();
    	}
    }

}