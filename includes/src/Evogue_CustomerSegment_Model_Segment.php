<?php
class Evogue_CustomerSegment_Model_Segment extends Mage_Rule_Model_Rule {

    const VIEW_MODE_UNION_CODE      = 'union';
    const VIEW_MODE_INTERSECT_CODE  = 'intersect';

    protected function _construct() {
        parent::_construct();
        $this->_init('evogue_customersegment/segment');
    }

    public function getConditionsInstance() {
        return Mage::getModel('evogue_customersegment/segment_condition_combine_root');
    }

    public function getWebsiteIds() {
        if (!$this->hasData('website_ids')) {
            $this->setData('website_ids', $this->_getResource()->getWebsiteIds($this->getId()));
        }
        return $this->_getData('website_ids');
    }

    protected function _afterLoad() {
        Mage_Core_Model_Abstract::_afterLoad();
        $conditionsArr = unserialize($this->getConditionsSerialized());
        if (!empty($conditionsArr) && is_array($conditionsArr)) {
            $this->getConditions()->loadArray($conditionsArr);
        }
        return $this;
    }

    protected function _beforeSave() {
        if (!$this->getData('processing_frequency')){
            $this->setData('processing_frequency', '1');
        }

        $events = array();
        if ($this->getIsActive()) {
            $events = $this->collectMatchedEvents();
        }
        $customer = new Zend_Db_Expr(':customer_id');
        $website = new Zend_Db_Expr(':website_id');
        $this->setConditionSql(
            $this->getConditions()->getConditionsSql($customer, $website)
        );
        $this->setMatchedEvents(array_unique($events));
        parent::_beforeSave();
    }

    protected function _prepareWebsiteIds() {
        return $this;
    }

    public function collectMatchedEvents($conditionsCombine = null) {
        $events = array();
        if ($conditionsCombine === null) {
            $conditionsCombine = $this->getConditions();
        }
        $matchedEvents = $conditionsCombine->getMatchedEvents();
        if (!empty($matchedEvents)) {
            $events = array_merge($events, $matchedEvents);
        }
        $children = $conditionsCombine->getConditions();
        if ($children) {
            if (!is_array($children)) {
                $children = array($children);
            }
            foreach ($children as $child) {
                $events = array_merge($events, $this->collectMatchedEvents($child));
            }
        }
        $events = array_unique($events);
        return $events;
    }

    public function getConditionModels($conditions = null) {
        $models = array();

        if (is_null($conditions)) {
            $conditions = $this->getConditions();
        }

        $models[] = $conditions->getType();
        $childConditions = $conditions->getConditions();
        if ($childConditions) {
            if (is_array($childConditions)) {
                foreach ($childConditions as $child) {
                    $models = array_merge($models, $this->getConditionModels($child));
                }
            } else {
                $models = array_merge($models, $this->getConditionModels($childConditions));
            }
        }

        return $models;
    }

    public function validate(Varien_Object $object) {
        $website = Mage::app()->getWebsite();
        if ($object instanceof Mage_Customer_Model_Customer) {
            return $this->validateCustomer($object, $website);
        }
        return false;
    }

    public function validateCustomer($customer, $website) {

        $sql = $this->getConditionSql();
        if (!$sql) {
            return false;
        }
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customerId = $customer->getId();
        } else {
            $customerId = $customer;
        }
        $website = Mage::app()->getWebsite($website);
        $params = array();
        if (strpos($sql, ':customer_id')) {
            $params['customer_id']  = $customerId;
        }
        if (strpos($sql, ':website_id')) {
            $params['website_id']   = $website->getId();
        }
        $result = $this->getResource()->runConditionSql($sql, $params);
        return $result>0;
    }

    public function matchCustomers() {
        $websiteIds = $this->getWebsiteIds();
        $queries = array();
        foreach ($websiteIds as $websiteId) {
            $queries[$websiteId] = $this->getConditions()->getConditionsSql(null, $websiteId);
        }
        $this->_getResource()->beginTransaction();
        $this->_getResource()->deleteSegmentCustomers($this);
        try {
            foreach ($queries as $websiteId => $query) {
                $this->_getResource()->saveCustomersFromSelect($this, $websiteId, $query);
            }
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
        }
        return $this;
    }
}
