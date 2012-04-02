<?php
class Evogue_CustomerSegment_Model_Mysql4_Segment extends Mage_Core_Model_Mysql4_Abstract {

    protected $_websiteTable;

    protected function _construct () {
        $this->_init('evogue_customersegment/segment', 'segment_id');
        $this->_websiteTable = $this->getTable('evogue_customersegment/website');
    }

    public function getWebsiteIds($segmentId) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_websiteTable, 'website_id')
            ->where('segment_id=?', $segmentId);
        return $this->_getReadAdapter()->fetchCol($select);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $id = $object->getId();
        $this->_getWriteAdapter()->delete(
            $this->getTable('evogue_customersegment/event'),
            array('segment_id = ?' => $id)
        );
        if ($object->getMatchedEvents() && is_array($object->getMatchedEvents())) {
            foreach ($object->getMatchedEvents() as $event) {
                $data = array(
                    'segment_id' => $id,
                    'event'      => $event,
                );
                $this->_getWriteAdapter()->insert($this->getTable('evogue_customersegment/event'), $data);
            }
        }

        if ($object->hasData('website_ids')) {
            $this->_saveWebsiteIds($object);
        }

        return parent::_afterSave($object);
    }

    protected function _saveWebsiteIds($segment) {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->_websiteTable, array('segment_id=?'=>$segment->getId()));
        foreach ($segment->getWebsiteIds() as $websiteId) {
            $adapter->insert($this->_websiteTable, array(
                'website_id' => $websiteId,
                'segment_id' => $segment->getId()
            ));
        }
        return $this;
    }

    public function runConditionSql($sql, $bindParams) {
        return $this->_getReadAdapter()->fetchOne($sql, $bindParams);
    }

    public function createSelect() {
        return $this->_getReadAdapter()->select();
    }

    public function quoteInto($string, $param) {
        return $this->_getReadAdapter()->quoteInto($string, $param);
    }

    public function getSqlOperator($operator) {

        switch ($operator) {
            case '==':
                return '=';
            case '!=':
                return '<>';
            case '{}':
                return 'LIKE';
            case '!{}':
                return 'NOT LIKE';
            case '[]':
                return 'FIND_IN_SET(%s, %s)';
            case '![]':
                return 'FIND_IN_SET(%s, %s) IS NULL';
            case 'between':
                return "BETWEEN '%s' AND '%s'";
            case '>':
            case '<':
            case '>=':
            case '<=':
                return $operator;
            default:
                Mage::throwException(Mage::helper('evogue_customersegment')->__('Unknown operator specified.'));
        }
    }

    public function createConditionSql($field, $operator, $value) {
        $sqlOperator = $this->getSqlOperator($operator);
        $condition = '';
        switch ($operator) {
            case '{}':
            case '!{}':
                if (is_array($value)) {
                    if (!empty($value)) {
                        $sqlOperator = ($operator == '{}') ? 'IN' : 'NOT IN';
                        $condition = $this->_getReadAdapter()->quoteInto(
                            $field.' '.$sqlOperator.' (?)', $value
                        );
                    }
                } else {
                    $condition = $this->_getReadAdapter()->quoteInto(
                        $field.' '.$sqlOperator.' ?', '%'.$value.'%'
                    );
                }
                break;
            case '[]':
            case '![]':
                if (is_array($value) && !empty($value)) {
                    $format = 'FIND_IN_SET(%s, %s)';
                    $conditions = array();
                    foreach ($value as $v) {
                        $conditions[] = sprintf($format, $this->_getReadAdapter()->quote($v), $field);
                    }
                    $condition  = sprintf('(%s)=%d', join(' AND ', $conditions), $operator == '[]' ? 1 : 0);
                } else {
                    if ($operator == '[]') {
                        $format = 'FIND_IN_SET(%s, %s)';
                    } else {
                        $format = 'FIND_IN_SET(%s, %s) IS NULL';
                    }
                    $condition = sprintf($format, $this->_getReadAdapter()->quote($value), $field);
                }
                break;
            case 'between':
                $condition = $field.' '.sprintf($sqlOperator, $value['start'], $value['end']);
                break;
            default:
                $condition = $this->_getReadAdapter()->quoteInto(
                    $field.' '.$sqlOperator.' ?', $value
                );
                break;
        }
        return $condition;
    }

    public function deleteSegmentCustomers($segment) {
        $this->_getWriteAdapter()->delete(
            $this->getTable('evogue_customersegment/customer'),
            array('segment_id=?' => $segment->getId())
        );
        return $this;
    }

    public function saveCustomersFromSelect($segment, $websiteId, $select) {
        $table = $this->getTable('evogue_customersegment/customer');
        $adapter = $this->_getWriteAdapter();
        $segmentId = $segment->getId();
        $now = $this->formatDate(time());

        $data = array();
        $count= 0;
        $stmt = $adapter->query($select);
        while ($row = $stmt->fetch()) {
            $data[] = array(
                'segment_id'    => $segmentId,
                'customer_id'   => $row['entity_id'],
                'website_id'    => $websiteId,
                'added_date'    => $now,
                'updated_date'  => $now,
            );
            $count++;
            if ($count>1000) {
                $count = 0;
                $adapter->insertMultiple($table, $data);
                $data = array();
            }
        }
        if ($count>0) {
            $adapter->insertMultiple($table, $data);
        }
        return $this;
    }

    public function getSegmentCustomersQty($segmentId) {
        return (int)$this->_getReadAdapter()->fetchOne("SELECT COUNT(DISTINCT customer_id)
            FROM {$this->getTable('evogue_customersegment/customer')} WHERE segment_id = " . (int)$segmentId);
    }

    public function saveSegmentCustomersFromSelect($segment, $select) {
        $table = $this->getTable('evogue_customersegment/customer');
        $adapter = $this->_getWriteAdapter();
        $segmentId = $segment->getId();
        $now = $this->formatDate(time());

        $adapter->delete($table, $adapter->quoteInto('segment_id=?', $segmentId));

        $data = array();
        $count= 0;
        $stmt = $adapter->query($select);
        while ($row = $stmt->fetch()) {
            $data[] = array(
                'segment_id'    => $segmentId,
                'customer_id'   => $row['entity_id'],
                'added_date'    => $now,
                'updated_date'  => $now,
            );
            $count++;
            if ($count>1000) {
                $count = 0;
                $adapter->insertMultiple($table, $data);
                $data = array();
            }
        }
        if ($count>0) {
            $adapter->insertMultiple($table, $data);
        }
        return $this;
    }
}
