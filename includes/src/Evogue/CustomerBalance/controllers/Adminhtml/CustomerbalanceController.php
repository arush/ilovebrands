<?php
class Evogue_CustomerBalance_Adminhtml_CustomerbalanceController extends Mage_Adminhtml_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            if ($this->getRequest()->getActionName() != 'noroute') {
                $this->_forward('noroute');
            }
        }
        return $this;
    }

    public function formAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridHistoryAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('evogue_customerbalance/adminhtml_customer_edit_tab_customerbalance_balance_history_grid')->toHtml()
        );
    }

    public function deleteOrphanBalancesAction() {
        $balance = Mage::getSingleton('evogue_customerbalance/balance')->deleteBalancesByCustomerId(
            (int)$this->getRequest()->getParam('id')
        );
        $this->_redirect('*/customer/edit/', array('_current'=>true));
    }

    protected function _initCustomer($idFieldName = 'id') {
        $customer = Mage::getModel('customer/customer')->load((int)$this->getRequest()->getParam($idFieldName));
        if (!$customer->getId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Failed to initialize customer'));
        }
        Mage::register('current_customer', $customer);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
