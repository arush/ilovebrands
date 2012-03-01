<?php

class Evogue_CustomerBalance_Model_Balance extends Mage_Core_Model_Abstract {

    protected $_customer;

    protected $_eventPrefix = 'customer_balance';
    protected $_eventObject = 'balance';

    protected function _construct() {
        $this->_init('evogue_customerbalance/balance');
    }

    public function shouldCustomerHaveOneBalance($customer) {
        return false;
    }

    public function getAmount() {
        return (float)$this->getData('amount');
    }

    public function loadByCustomer() {
        $this->_ensureCustomer();
        if ($this->hasWebsiteId()) {
            $websiteId = $this->getWebsiteId();
        }
        else {
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::throwException(Mage::helper('evogue_customerbalance')->__('Website ID must be set.'));
            }
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        $this->getResource()->loadByCustomerAndWebsiteIds($this, $this->getCustomerId(), $websiteId);
        return $this;
    }

    public function setNotifyByEmail($shouldNotify, $storeId = null) {
        $this->setData('notify_by_email', $shouldNotify);
        if ($shouldNotify) {
            if (null === $storeId) {
                Mage::throwException(Mage::helper('evogue_customerbalance')->__('Please set store ID as well.'));
            }
            $this->setStoreId($storeId);
        }
        return $this;

    }

    protected function _beforeSave() {
        $this->_ensureCustomer();

        // make sure appropriate website was set. Admin website is disallowed
        if ((!$this->hasWebsiteId()) && $this->shouldCustomerHaveOneBalance($this->getCustomer())) {
            $this->setWebsiteId($this->getCustomer()->getWebsiteId());
        }
        if (0 == $this->getWebsiteId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Website ID must be set.'));
        }

        // check history action
        if (!$this->getId()) {
            $this->loadByCustomer();
            if (!$this->getId()) {
                $this->setHistoryAction(Evogue_CustomerBalance_Model_Balance_History::ACTION_CREATED);
            }
        }
        if (!$this->hasHistoryAction()) {
            $this->setHistoryAction(Evogue_CustomerBalance_Model_Balance_History::ACTION_UPDATED);
        }

        // check balance delta and email notification settings
        $delta = $this->_prepareAmountDelta();
        if (0 == $delta) {
            $this->setNotifyByEmail(false);
        }
        if ($this->getNotifyByEmail() && !$this->hasStoreId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('In order to send email notification, the Store ID must be set.'));
        }

        return parent::_beforeSave();
    }

    protected function _afterSave() {
        parent::_afterSave();

        // save history action
        if (abs($this->getAmountDelta())) {
            $history = Mage::getModel('evogue_customerbalance/balance_history')
                ->setBalanceModel($this)
                ->save();
        }

        return $this;
    }

    protected function _ensureCustomer() {
        if ($this->getCustomer() && $this->getCustomer()->getId()) {
            $this->setCustomerId($this->getCustomer()->getId());
        }
        if (!$this->getCustomerId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Customer ID must be specified.'));
        }
        if (!$this->getCustomer()) {
            $this->setCustomer(Mage::getModel('customer/customer')->load($this->getCustomerId()));
        }
        if (!$this->getCustomer()->getId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Customer is not set or does not exist.'));
        }
    }

    protected function _prepareAmountDelta() {
        $result = 0;
        if ($this->hasAmountDelta()) {
            $result = (float)$this->getAmountDelta();
            if ($this->getId()) {
                if (($result < 0) && (($this->getAmount() + $result) < 0)) {
                    $result = -1 * $this->getAmount();
                }
            }
            elseif ($result <= 0) {
                $result = 0;
            }
        }
        $this->setAmountDelta($result);
        if (!$this->getId()) {
            $this->setAmount($result);
        }
        else {
            $this->setAmount($this->getAmount() + $result);
        }
        return $result;
    }

    public function isFullAmountCovered(Mage_Sales_Model_Quote $quote, $isEstimation = false) {
        if (!$isEstimation && !$quote->getUseCustomerBalance()) {
            return false;
        }
        return $this->getAmount() >=
            ((float)$quote->getBaseGrandTotal() + (float)$quote->getBaseCustomerBalanceAmountUsed());
    }

    public function isFulAmountCovered(Mage_Sales_Model_Quote $quote) {
        return $this->isFullAmountCovered($quote);
    }

    public function setCustomersBalanceCurrencyTo($websiteId, $currencyCode) {
        $this->getResource()->setCustomersBalanceCurrencyTo($websiteId, $currencyCode);
        return $this;
    }

    public function deleteBalancesByCustomerId($customerId) {
        $this->getResource()->deleteBalancesByCustomerId($customerId);
        return $this;
    }

    public function getOrphanBalancesCount($customerId) {
        return $this->getResource()->getOrphanBalancesCount($customerId);
    }

    public function afterLoad() {
        return $this->_afterLoad();
    }
}
