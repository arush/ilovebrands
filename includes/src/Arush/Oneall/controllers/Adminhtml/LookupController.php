<?php

class Arush_Oneall_Adminhtml_LookupController extends Mage_Adminhtml_Controller_action {

	public function rpAction() {
		if(Mage::helper('oneall/rpxcall')->rpxLookupSave())
			Mage::getSingleton('core/session')->addSuccess('Oneall account data successfully retrieved');
		else
			Mage::getSingleton('core/session')->addError('Oneall account data could not be updated');
		$this->_redirect('adminhtml/system_config/edit/section/oneall');
	}

}