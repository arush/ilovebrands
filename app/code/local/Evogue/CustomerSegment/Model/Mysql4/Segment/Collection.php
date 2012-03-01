<?php
class Evogue_CustomerSegment_Model_Mysql4_Segment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_customerCountAdded = false;

    protected function _construct() {
        $this->_init('evogue_customersegment/segment');
    }

    public function addIsActiveFilter($value) {
        $this->getSelect()->where('main_table.is_active = ?', $value);
        return $this;
    }

    protected function _beforeLoad() {
        $isFilteredByWebsite = $this->getFlag('is_filtered_by_website');
        $isOrderedByWebsite = array_key_exists('website_ids', $this->_orders);
        if (($isFilteredByWebsite || $isOrderedByWebsite) && !$this->getFlag('is_website_table_joined')) {
            $this->setFlag('is_website_table_joined', true);
            $join = ($isFilteredByWebsite ? 'joinInner' : 'joinLeft');
            $cols = ($isOrderedByWebsite ? array('website_ids' => 'website.website_id') : array());
            $this->getSelect()->$join(
                array('website' => $this->getTable('evogue_customersegment/website')),
                'main_table.segment_id = website.segment_id',
                $cols
            );
        }
        return parent::_beforeLoad();
    }

    protected function _afterLoad() {
        parent::_afterLoad();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('evogue_customersegment/website'), array(
                    'segment_id',
                    new Zend_Db_Expr('GROUP_CONCAT(website_id)')
                ))
                ->where('segment_id IN (?)', array_keys($this->_items))
                ->group('segment_id');
            $websites = $this->getConnection()->fetchPairs($select);
            foreach ($this->_items as $item) {
                if (isset($websites[$item->getId()])) {
                    $item->setWebsiteIds(explode(',', $websites[$item->getId()]));
                }
            }
        }

        return $this;
    }

    public function addWebsitesToResult($flag = null) {
        $flag = ($flag === null) ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    public function addEventFilter($eventName) {
        if (!$this->getFlag('is_event_table_joined')) {
            $this->setFlag('is_event_table_joined', true);
            $this->getSelect()->joinInner(
                array('evt'=>$this->getTable('evogue_customersegment/event')),
                'main_table.segment_id = evt.segment_id',
                array()
            );
        }
        $this->getSelect()->where('evt.event = ?', $eventName);
        return $this;
    }

    public function addWebsiteFilter($websiteId) {
        $this->setFlag('is_filtered_by_website', true);
        if ($websiteId instanceof Mage_Core_Model_Website) {
            $websiteId = $websiteId->getId();
        }
        $this->getSelect()->where('website.website_id IN (?)', $websiteId);
        return $this;
    }

    public function addFieldToFilter($field, $condition=null) {
        if ($field == 'website_ids') {
            return $this->addWebsiteFilter($condition);
        } else if ($field == $this->getResource()->getIdFieldName()) {
            $field = 'main_table.' . $field;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    public function toOptionArray() {
        return $this->_toOptionArray('segment_id', 'name');
    }

    public function getSelectCountSql() {
        $countSelect = parent::getSelectCountSql();
        if ($this->_customerCountAdded) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->reset(Zend_Db_Select::HAVING);
            $countSelect->resetJoinLeft();
        }
        return $countSelect;
    }

    public function addCustomerCountToSelect() {
        if ($this->_customerCountAdded) {
            return $this;
        }
        $this->_customerCountAdded = true;
        $this->getSelect()
            ->joinLeft(
                array('customer_count_table' => $this->getTable('evogue_customersegment/customer')),
                'customer_count_table.segment_id = main_table.segment_id',
                array('customer_count' => new Zend_Db_Expr('COUNT(customer_count_table.customer_id)'))
            )
            ->group('main_table.segment_id');
        return $this;
    }

    public function addCustomerCountFilter($customerCount) {
        $this->addCustomerCountToSelect();
        $this->getSelect()
            ->having('`customer_count` = ?', $customerCount);
        return $this;
    }

    public function getAllIds() {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select = $this->getConnection()->select()->from(array('t' => $idsSelect), array(
            't.' . $this->getResource()->getIdFieldName()
        ));
        return $this->getConnection()->fetchCol($select, $this->_bindParams);
    }
}
