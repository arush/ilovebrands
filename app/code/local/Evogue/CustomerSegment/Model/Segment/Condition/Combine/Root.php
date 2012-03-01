<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Combine_Root
    extends Evogue_CustomerSegment_Model_Segment_Condition_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_combine_root');
    }

    public function getMatchedEvents() {
        return array('customer_login');
    }

    protected function _createCustomerFilter($customer, $fieldName) {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = $customer->getId();
        } else if ($customer instanceof Zend_Db_Select) {
            $customer = new Zend_Db_Expr($customer);
        }

        return $this->getResource()->quoteInto("{$fieldName} IN (?)", $customer);
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();
        $table = array('root' => $this->getResource()->getTable('customer/entity'));
        $select->from($table, array('entity_id'));
        if ($customer) {
            $select->where($this->_createCustomerFilter($customer, 'entity_id'));
        } elseif ($customer === null) {
            if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
                $select->where('website_id=?', $website);
            }
        }
        return $select;
    }

    public function getConditionsSql($customer, $website) {
        $select     = $this->_prepareConditionsSql($customer, $website);
        $required   = $this->_getRequiredValidation();
        $aggregator = ($this->getAggregator() == 'all') ? ' AND ' : ' OR ';
        $operator   = $required ? '=' : '<>';
        $conditions = array();

        foreach ($this->getConditions() as $condition) {
            if ($sql = $condition->getConditionsSql($customer, $website)) {
                $conditions[] = "(IFNULL(($sql), 0) {$operator} 1)";
            }
        }

        $subfilterMap = $this->_getSubfilterMap();
        if ($subfilterMap) {
            foreach ($this->getConditions() as $condition) {
                $subfilterType = $condition->getSubfilterType();
                if (isset($subfilterMap[$subfilterType])) {
                    $subfilter = $condition->getSubfilterSql($subfilterMap[$subfilterType], $required, $website);
                    if ($subfilter) {
                        $conditions[] = $subfilter;
                    }
                }
            }
        }

        if (!empty($conditions)) {
            $select->where(implode($aggregator, $conditions));
        }
        return $select;
    }
}
