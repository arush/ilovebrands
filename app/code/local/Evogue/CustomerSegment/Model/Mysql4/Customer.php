<?php
class Evogue_CustomerSegment_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct () {
        $this->_init('evogue_customersegment/customer', 'customer_id');
    }

    public function addCustomerToWebsiteSegments($customerId, $websiteId, $segmentIds) {
        $data = array();
        $now = $this->formatDate(time(), true);
        foreach ($segmentIds as $segmentId) {
            $data = array(
                'segment_id'    => $segmentId,
                'customer_id'   => $customerId,
                'added_date'    => $now,
                'updated_date'  => $now,
                'website_id'    => $websiteId,
            );
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data, array('updated_date'));
        }
        return $this;
    }

    public function removeCustomerFromWebsiteSegments($customerId, $websiteId, $segmentIds) {
        if (!empty($segmentIds)) {
            $this->_getWriteAdapter()->delete($this->getMainTable(), array(
                'customer_id=?'     => $customerId,
                'website_id=?'      => $websiteId,
                'segment_id IN(?)'  => $segmentIds
            ));
        }
        return $this;
    }


    public function getCustomerWebsiteSegments($customerId, $websiteId) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'segment_id')
            ->where('customer_id = ?', $customerId)
            ->where('website_id = ?', $websiteId);
        return $this->_getReadAdapter()->fetchCol($select);
    }

    public function addCustomerToSegments($customerId, $segmentIds) {
        $data = array();
        $now = $this->formatDate(time(), true);
        foreach ($segmentIds as $segmentId) {
            $data = array(
                'segment_id'    => $segmentId,
                'customer_id'   => $customerId,
                'added_date'    => $now,
                'updated_date'  => $now,
            );
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data, array('updated_date'));
        }
        return $this;
    }

    public function removeCustomerFromSegments($customerId, $segmentIds) {
        if (!empty($segmentIds)) {
            $adapter = $this->_getWriteAdapter();
            $condition = $adapter->quoteInto('customer_id=? AND ', $customerId)
                . $adapter->quoteInto('segment_id IN (?)', $segmentIds);
            $adapter->delete($this->getMainTable(), $condition);
        }
        return $this;
    }

    public function getCustomerSegments($customerId) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'segment_id')
            ->where('customer_id = ?', $customerId);
        return $this->_getReadAdapter()->fetchCol($select);
    }
}
