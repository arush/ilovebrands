<?php
class Evogue_CustomerBalance_Model_Mysql4_Balance extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('evogue_customerbalance/balance', 'balance_id');
    }

    public function loadByCustomerAndWebsiteIds($object, $customerId, $websiteId) {
        if ($data = $this->getReadConnection()->fetchRow($this->getReadConnection()->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->where('website_id = ?', $websiteId)
            ->limit(1))) {
            $object->addData($data);
        }
    }

    public function setCustomersBalanceCurrencyTo($websiteId, $currencyCode) {
        $bind = array('base_currency_code' => $currencyCode);
        $this->_getWriteAdapter()->update(
            $this->getMainTable(), $bind,
            array('website_id=?' => $websiteId, 'base_currency_code IS NULL')
        );
        return $this;
    }

    public function deleteBalancesByCustomerId($customerId) {
        $adapter = $this->_getWriteAdapter();

        $adapter->delete(
            $this->getMainTable(), $adapter->quoteInto('customer_id = ? AND website_id IS NULL', $customerId)
        );
        return $this;
    }

    public function getOrphanBalancesCount($customerId) {
        $adapter = $this->_getReadAdapter();
        return $adapter->fetchOne($adapter->select()
            ->from($this->getMainTable(), 'count(*)')
            ->where('customer_id = ?', $customerId)
            ->where('website_id IS NULL'));
    }
}
