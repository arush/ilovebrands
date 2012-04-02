<?php

class Ebizmarts_SagePaySuite_Adminhtml_RepeatpaymentController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
        $this->loadLayout('popup_sagepay')
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction'))
            ->renderLayout();
	}

	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction_grid')->toHtml()
        );
	}

}