<?php
class Evogue_CustomerBalance_Block_Checkout_Onepage_Payment_Additional extends Mage_Core_Block_Template {
    protected $_balanceModel = null;

    protected function _getQuote() {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getQuote() {
        return $this->_getQuote();
    }

    protected function _getBalanceModel() {
        if (is_null($this->_balanceModel)) {
            $this->_balanceModel = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomer($this->_getCustomer())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            //load customer balance for customer in case we have
            //registered customer and this is not guest checkout
            if ($this->_getCustomer()->getId()) {
                $this->_balanceModel->loadByCustomer();
            }
        }
        return $this->_balanceModel;
    }

    protected function _getCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function isDisplayContainer() {
        if (!$this->_getCustomer()->getId()) {
            return false;
        }

        if (!$this->getBalance()) {
            return false;
        }

        return true;
    }

    public function isAllowed() {
        if (!$this->isDisplayContainer()) {
            return false;
        }

        if (!$this->getAmountToCharge()) {
            return false;
        }

        return true;
    }

    public function getBalance() {
        if (!$this->_getCustomer()->getId()) {
            return 0;
        }
        return $this->_getBalanceModel()->getAmount();
    }

    public function getAmountToCharge() {
        if ($this->isCustomerBalanceUsed()) {
            return $this->_getQuote()->getCustomerBalanceAmountUsed();
        }

        return min($this->getBalance(), $this->_getQuote()->getBaseGrandTotal());
    }

    public function isCustomerBalanceUsed() {
        return $this->_getQuote()->getUseCustomerBalance();
    }

    public function isFullyPaidAfterApplication() {
        return $this->_getBalanceModel()->isFullAmountCovered($this->_getQuote(), true);
    }
}