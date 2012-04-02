<?php
class Evogue_CustomerSegment_Model_Mysql4_Report_Customer_Collection
    extends Mage_Customer_Model_Entity_Customer_Collection {

    protected $_viewMode;

    protected $_subQuery = null;
    protected $_websites = null;

    public function addSegmentFilter($segment) {
        if ($segment instanceof Evogue_CustomerSegment_Model_Segment) {
            $segment = ($segment->getId()) ? $segment->getId() : $segment->getMassactionIds();
        }

        $this->_subQuery = ($this->getViewMode() == Evogue_CustomerSegment_Model_Segment::VIEW_MODE_INTERSECT_CODE)
            ? $this->_getIntersectQuery($segment)
            : $this->_getUnionQuery($segment);

        return $this;
    }

    public function addWebsiteFilter($websites) {
        if (is_null($websites)) {
            return $this;
        }
        if (!is_array($websites)) {
            $websites = array($websites);
        }
        $this->_websites = array_unique($websites);
        return $this;
    }

    protected function _getUnionQuery($segment) {
        $select = clone $this->getSelect();
        $select->reset();
        $select->from(
            $this->getTable('evogue_customersegment/customer'),
            'customer_id'
        )
        ->where('segment_id IN(?)', $segment)
        ->where('e.entity_id = customer_id');
        return $select;
    }

    protected function _getIntersectQuery($segment) {
        $select = clone $this->getSelect();
        $select->reset();
        $select->from(
            $this->getTable('evogue_customersegment/customer'),
            'customer_id'
        )
        ->where('segment_id IN(?)', $segment)
        ->where('e.entity_id = customer_id')
        ->group('customer_id')
        ->having('COUNT(segment_id) = ?', count($segment));
        return $select;
    }

    public function setViewMode($mode) {
        $this->_viewMode = $mode;
        return $this;
    }

    public function getViewMode() {
        return $this->_viewMode;
    }

    protected function _applyFilters() {
        if (!is_null($this->_websites)) {
            $this->_subQuery->where('website_id IN(?)', $this->_websites);
        }
        $this->getSelect()->where('e.entity_id IN(?)', new Zend_Db_Expr($this->_subQuery));
        return $this;
    }

    protected function _beforeLoad() {
        $this->_applyFilters();
        return $this;
    }
}
