<?php

/**
 * Admin TOKEN grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Adminhtml_TokenController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Sage Pay Cards'), $this->__('Sage Pay Cards'));
        return $this;
    }

	public function indexAction()
	{
        $this->_title($this->__('Sales'))->_title($this->__('Sage Pay Cards'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_tokencard'))
            ->renderLayout();
	}

	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_tokencard_grid')->toHtml()
        );
	}


	public function deleteCardAction()
	{
		$card = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($this->getRequest()->getParam('id'));

		if($card->getId()){
			$result = Mage::getModel('sagepaysuite/sagePayToken')->removeCard($card->getToken());

			if($result['Status'] == 'OK'){
			  //Delete card on our Database
			  $card->delete();
			  $this->_getSession()->addSuccess($this->__('Card deleted successfully'));
			}else{
			  $this->_getSession()->addError($this->__($result['StatusDetail']));
			}

			$this->_redirectReferer();
			return;
		}

		$this->_getSession()->addError($this->__('This card does not exist.'));
		$this->_redirectReferer();
		return;
	}
}