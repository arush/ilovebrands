<?php
class Evogue_CustomerBalance_InfoController extends Mage_Core_Controller_Front_Action {
    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction() {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            $this->_redirect('customer/account/');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->loadLayoutUpdates();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('evogue_customerbalance')->__('Store Credit'));
        }
        $this->renderLayout();
    }
}
