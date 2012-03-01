<?php
class Evogue_TargetRule_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract {
    
    protected function _construct() {
        $this->_init('evogue_targetrule/rule', 'rule_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if ($object->getFromDate() instanceof Zend_Date) {
            $object->setFromDate($object->getFromDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $object->setFromDate(null);
        }

        if ($object->getToDate() instanceof Zend_Date) {
            $object->setToDate($object->getToDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $object->setToDate(null);
        }
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        parent::_afterSave($object);
        $this->_prepareRuleProducts($object);

        if (!$object->isObjectNew() && $object->getOrigData('apply_to') != $object->getData('apply_to')) {
            Mage::getResourceModel('evogue_targetrule/index')
                ->cleanIndex();
        } else {
            Mage::getResourceModel('evogue_targetrule/index')
                ->cleanIndex($object->getData('apply_to'));
        }

        return $this;
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        Mage::getResourceModel('evogue_targetrule/index')
            ->cleanIndex($object->getData('apply_to'));

        return parent::_beforeDelete($object);
    }

    protected function _getCustomerSegmentRelationsTable() {
        return $this->getTable('evogue_targetrule/customersegment');
    }

    protected function _getRuleProductsTable() {
        return $this->getTable('evogue_targetrule/product');
    }

    public function getCustomerSegmentRelations($ruleId) {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_getCustomerSegmentRelationsTable(), 'segment_id')
            ->where('rule_id=?', $ruleId);
        return $adapter->fetchCol($select);
    }

    protected function _saveCustomerSegmentRelations(Mage_Core_Model_Abstract $object) {
        $adapter = $this->_getWriteAdapter();
        $ruleId  = $object->getId();
        if (!$object->getUseCustomerSegment()) {
            $adapter->delete($this->_getCustomerSegmentRelationsTable(), array('rule_id=?' => $ruleId));
            return $this;
        }

        $old = $this->getCustomerSegmentRelations($ruleId);
        $new = $object->getCustomerSegmentRelations();

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $segmentId) {
                $data[] = array(
                    'rule_id'       => $ruleId,
                    'segment_id'    => $segmentId
                );
            }
            $adapter->insertMultiple($this->_getCustomerSegmentRelationsTable(), $data);
        }

        if (!empty($delete)) {
            $where = join(' AND ', array(
                $adapter->quoteInto('rule_id=?', $ruleId),
                $adapter->quoteInto('segment_id IN(?)', $delete)
            ));
            $adapter->delete($this->_getCustomerSegmentRelationsTable(), $where);
        }

        return $this;
    }

    protected function _prepareRuleProducts($object) {
        $adapter = $this->_getWriteAdapter();

        $ruleId  = $object->getId();
        $adapter->delete($this->_getRuleProductsTable(), array('rule_id=?' => $ruleId));

        $chunk = array_chunk($object->getMatchingProductIds(), 1000);
        foreach ($chunk as $productIds) {
            $data = array();
            foreach ($productIds as $productId) {
                $data[] = array(
                    'rule_id'       => $ruleId,
                    'product_id'    => $productId,
                    'store_id'      => 0
                );
            }
            if ($data) {
                $adapter->insertMultiple($this->_getRuleProductsTable(), $data);
            }
        }

        return $this;
    }

    public function addCustomerSegmentRelationsToCollection(Mage_Core_Model_Mysql4_Collection_Abstract $collection) {
        $ruleIds    = array_keys($collection->getItems());
        $segments   = array();
        if ($ruleIds) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select()
                ->from($this->_getCustomerSegmentRelationsTable())
                ->where('rule_id IN(?)', $ruleIds);
            $rowSet = $adapter->fetchAll($select);

            foreach ($rowSet as $row) {
                $segments[$row['rule_id']][$row['segment_id']] = $row['segment_id'];
            }
        }

        foreach ($collection->getItems() as $rule) {
            if ($rule->getUseCustomerSegment()) {
                $data = isset($segments[$rule->getId()]) ? $segments[$rule->getId()] : array();
                $rule->setCustomerSegmentRelations($data);
            }
        }

        return $this;
    }
}
