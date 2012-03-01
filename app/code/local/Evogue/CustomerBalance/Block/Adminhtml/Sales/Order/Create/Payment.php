<?php
class Evogue_CustomerBalance_Block_Adminhtml_Sales_Order_Create_Payment
extends Mage_Core_Block_Template {
    protected $_balanceInstance;

    protected function _getOrderCreateModel() {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    public function formatPrice($value) {
        return Mage::getSingleton('adminhtml/session_quote')->getStore()->formatPrice($value);
    }

    public function getBalance() {
        if (!Mage::helper('evogue_customerbalance')->isEnabled() || !$this->_getBalanceInstance()) {
            return 0.0;
        }
        return $this->_getBalanceInstance()->getAmount();
    }

    public function getUseCustomerBalance() {
        return $this->_getOrderCreateModel()->getQuote()->getUseCustomerBalance();
    }

    public function isFullyPaid() {
        if (!$this->_getBalanceInstance()) {
            return false;
        }
        return $this->_getBalanceInstance()->isFullAmountCovered($this->_getOrderCreateModel()->getQuote());
    }

    public function isUsed() {
        return $this->getUseCustomerBalance();
    }

    protected function _getBalanceInstance() {
        if (!$this->_balanceInstance) {
            $quote = $this->_getOrderCreateModel()->getQuote();
            if (!$quote || !$quote->getCustomerId() || !$quote->getStoreId()) {
                return false;
            }

            $store = Mage::app()->getStore($quote->getStoreId());
            $this->_balanceInstance = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomerId($quote->getCustomerId())
                ->setWebsiteId($store->getWebsiteId())
                ->loadByCustomer();
        }
        return $this->_balanceInstance;
    }
}
