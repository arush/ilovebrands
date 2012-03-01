<?php
class Evogue_CustomerBalance_Block_Account_History extends Mage_Core_Block_Template {

    protected $_actionNames = null;

    public function canShow() {
        return Mage::getStoreConfigFlag('customer/evogue_customerbalance/show_history');
    }

    public function getEvents() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return false;
        }

        $collection = Mage::getModel('evogue_customerbalance/balance_history')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId())
                ->setOrder('updated_at');

        return $collection;
    }

    public function getActionNames() {
        if (is_null($this->_actionNames)) {
            $this->_actionNames = Mage::getSingleton('evogue_customerbalance/balance_history')->getActionNamesArray();
        }
        return $this->_actionNames;
    }

    public function getActionLabel($action) {
        $names = $this->getActionNames();
        if (isset($names[$action])) {
            return $names[$action];
        }
        return '';
    }
}
