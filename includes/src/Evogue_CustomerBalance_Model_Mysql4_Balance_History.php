<?php
class Evogue_CustomerBalance_Model_Mysql4_Balance_History extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('evogue_customerbalance/balance_history', 'history_id');
    }

    public function _beforeSave(Mage_Core_Model_Abstract $object) {
        $object->setUpdatedAt($this->formatDate(time()));
        return parent::_beforeSave($object);
    }

    public function markAsSent($id) {
        $this->_getWriteAdapter()->update($this->getMainTable(), array('is_customer_notified' => 1),
            $this->_getWriteAdapter()->quoteInto('history_id = ?', $id)
        );
    }
}
