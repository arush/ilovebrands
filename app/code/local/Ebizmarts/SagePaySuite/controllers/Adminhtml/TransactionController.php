<?php

/**
 * Orhpans transactions controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
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
            ->_addBreadcrumb($this->__('Sage Pay Orphan Transactions'), $this->__('Sage Pay Orphan Transactions'));
        return $this;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initPaymentsAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Sage Pay Transactions'), $this->__('Sage Pay Transactions'));
        return $this;
    }

	protected function _getTransaction()
	{
		return Mage::getModel('sagepaysuite2/sagepaysuite_transaction');
	}

	/**
	 * Recover transaction, failed/uncompleted in SagePay
	 */
	public function recoverAction()
	{
		$paramVendor = $this->getRequest()->getParam('vendortxcode', null);

		try{

            $orderId = Mage::getModel('sagepaysuite/api_payment')->recoverTransaction($paramVendor);

            if($orderId !== false){

            	$this->_getSession()->addSuccess(Mage::helper('sagepaysuite')->__('Transaction %s successfully recovered.', $paramVendor));
				$this->_redirect('adminhtml/sales_order/view', array(
	                'order_id'=>$orderId
    	        ));
				return;

            }else{

            	$this->_getSession()->addError(Mage::helper('sagepaysuite')->__('Transaction %s couldn\'t be recovered.', $paramVendor));
				$this->_redirectReferer();
            	return;

            }

        }catch(Exception $e){
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer();
            return;
        }


	}

    public function deleteAction()
    {

         if($this->getRequest()->isPost()){ #Mass action

            $trnIds = $this->getRequest()->getPost('transaction_ids', array());
            foreach ($trnIds as $paramVendor) {

		        $trn = $this->_getTransaction()
		                ->load($paramVendor);
		        if($trn->getId()){
		            try{
		                $trn->delete();
		                $this->_getSession()->addSuccess(Mage::helper('sagepaysuite')->__('Transaction %s successfully deleted.', $paramVendor));
		            }catch(Exception $e){
		                $this->_getSession()->addError($e->getMessage());
		            }
		        }else{
		            $this->_getSession()->addError(Mage::helper('sagepaysuite')->__('Invalid VendorTxCode supplied, %s', $paramVendor));
		        }

            }

         }else{

	        $paramVendor = $this->getRequest()->getParam('vendortxcode');
	        $trn = $this->_getTransaction()
	                ->loadByVendorTxCode($paramVendor);
	        if($trn->getId()){
	            try{
	                $trn->delete();
	                $this->_getSession()->addSuccess(Mage::helper('sagepaysuite')->__('Transaction %s successfully deleted.', $paramVendor));
	            }catch(Exception $e){
	                $this->_getSession()->addError($e->getMessage());
	            }
	        }else{
	            $this->_getSession()->addError(Mage::helper('sagepaysuite')->__('Invalid VendorTxCode supplied, %s', $paramVendor));
	        }

        }

        $this->_redirectReferer();
        return;
    }

    public function voidAction()
    {
        $paramVendor = $this->getRequest()->getParam('vendortxcode');
        $trn = $this->_getTransaction()
                ->loadByVendorTxCode($paramVendor);
        if($trn->getId()){
            try{
                Mage::getModel('sagepaysuite/api_payment')->voidPayment($trn);
                $this->_getSession()->addSuccess(Mage::helper('sagepaysuite')->__('Transaction %s successfully VOIDed.', $paramVendor));
            }catch(Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }else{
            $this->_getSession()->addError(Mage::helper('sagepaysuite')->__('Invalid VendorTxCode supplied, %s', $paramVendor));
        }

        $this->_redirectReferer();
        return;
    }

	public function addApiDataAction()
	{
		$id = $this->getRequest()->getParam('order_id');
		Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->addApiDetails($id);

        $this->_redirectReferer();
        return;
	}

	/**
	 * Delete TRN from sagepaysuite_transactions table
	 */
	public function removetrnAction()
	{
		$trns = $this->getRequest()->getParam('ids');

		if(count($trns)){

			foreach($trns as $id){
				$trn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->load($id);

				if($trn->getId()){
					$action = Mage::getModel('sagepaysuite2/sagepaysuite_action')->getCollection()->addFieldToFilter('parent_id', $trn->getId());

					if($action->getSize()){
						foreach($action as $_a){
							$_a->delete();
						}
					}

					$paypal = Mage::getModel('sagepaysuite2/sagepaysuite_paypaltransaction')->loadByParent($trn->getId());
					if($paypal->getId()){
						$paypal->delete();
					}

					$trn->delete();

					$this->_getSession()->addSuccess($this->__('Transaction #%s deleted.', $id));
				}else{
					$this->_getSession()->addError($this->__('Transaction #%s does not exist.', $id));
				}
			}

		}
		$this->_redirectReferer();
	}

	public function paymentsAction()
	{
        $this->_title($this->__('Sales'))->_title($this->__('Sage Pay Transactions'));

        $this->_initPaymentsAction()
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction'))
            ->renderLayout();
	}

	public function paymentsGridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction_grid')->toHtml()
        );
	}

	public function orphanAction()
	{
        $this->_title($this->__('Sales'))->_title($this->__('Sage Pay Orphan Transactions'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_transaction'))
            ->renderLayout();
	}

	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_transaction_grid')->toHtml()
        );
	}
}
