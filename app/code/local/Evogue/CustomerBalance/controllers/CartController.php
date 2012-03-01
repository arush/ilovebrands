<?php
class Evogue_CustomerBalance_CartController extends Mage_Core_Controller_Front_Action {

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function removeAction() {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            $this->_redirect('customer/account/');
            return;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote->getUseCustomerBalance()) {
            Mage::getSingleton('checkout/session')->addSuccess(
                $this->__('The store credit payment has been removed from shopping cart.')
            );
            $quote->setUseCustomerBalance(false)->collectTotals()->save();
        } else {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Store Credit payment is not being used in your shopping cart.')
            );
        }

        $this->_redirect('checkout/cart');
    }
}
