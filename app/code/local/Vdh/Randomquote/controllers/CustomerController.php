<?php class Vdh_Randomquote_CustomerController extends Mage_Core_Controller_Front_Action {

	public function setgroupAction() {
	
		$customer = Mage::getModel('randomquote/customer')->setGroupId();
	}

}