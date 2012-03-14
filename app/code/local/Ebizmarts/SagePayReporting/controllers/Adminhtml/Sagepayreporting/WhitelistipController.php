<?php

class Ebizmarts_SagePayReporting_Adminhtml_Sagepayreporting_WhitelistipController extends Mage_Adminhtml_Controller_Action
{

	public function whitelistIpAction()
	{
		$this->_title($this->__('Sales'))
		->_title($this->__('SagePay Suite'))
		->_title($this->__('White list IP address'));

		$this->loadLayout()
		->_setActiveMenu('sales/sagepay')
		->_addContent($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_whitelistip_edit'));

		$this->renderLayout();
	}

	public function saveAction()
	{
		if(!$this->getRequest()->isPost()){
			$this->_redirect('sagepayreporting/adminhtml_sagepayreporting_whitelistip/whitelistIp');
			return;
		}

		$mapmode = array(1=>'Live', 2=>'Test');

		$post = $this->getRequest()->getPost();
		foreach($post['mode'] as $mode){
			$rs = Mage::getModel('sagepayreporting/sagepayreporting')->addValidIPs($post['vendor'], $post['ip_address'], $post['ip_mask'], $post['ip_note'], $mode, $post['api_username'], $post['api_password']);
			if((string)$rs->errorcode != '0000'){
				$this->_getSession()->addError(htmlentities(Mage::helper('sagepayreporting')->__('Error in %s mode:', $mapmode[$mode]) . ' ' . (string)$rs->error));
			}else{
				$this->_getSession()->addSuccess(Mage::helper('sagepayreporting')->__('IP address %s successfully added.', $post['ip_address']));
			}
		}

		$this->_redirectReferer();
		return;
	}

}