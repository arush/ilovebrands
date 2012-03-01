<?php

class Ebizmarts_SagePayReporting_Block_Adminhtml_Sagepayreporting_Avscvstatus extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sagepayreporting/avscvstatus.phtml');
    }

	public function getAccessModel()
	{
		return Mage::getModel('sagepayreporting/sagepayreporting');
	}

    public function getAvsCv2Status()
    {
    	try{
    		return $this->getAccessModel()->getAVSCV2Status();
    	}catch(Exception $e){
			return $e->getMessage();
    	}
    }

    public function getAvsCv2Rules()
    {
		try{
    		return $this->getAccessModel()->getAvsCv2Rules();
    	}catch(Exception $e){
			return $e->getMessage();
    	}
    }

}