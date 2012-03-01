<?php
class Vdh_Popup_FormController extends Mage_Core_Controller_Front_Action {

	public function countAction() {
		$count = 0;	
		foreach(Mage::helper('popup')->getUrls() as $url) {
			try {
				// We're scrapping curl and loading the block instead - This should check the Session and Cookie info intact for creating rules
				$template = explode('/', $url);
				$this->loadLayout();
		        $this->getLayout()->getBlock('root')->setTemplate('vdh/popup/'. $template[5] . '.phtml');
		        $html = $this->getLayout()->getBlock('root')->toHtml();
		        if (strlen($html) > 0) { $count++; }
			
			} catch(Exception $e) {
				print_r($e);
			}
		}
		print_r($count);
	}
	
	
	public function pageAction() {
		$formData = $this->getRequest()->getParams();		
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('vdh/popup/'. trim($formData['index'], '/') . '.phtml');
		$this->getLayout()->getBlock('root')->setData($formData);
    	$this->renderLayout();	
	}	
}
