<?php
abstract class Evogue_CustomerSegment_Model_Condition_Combine_Abstract extends Mage_Rule_Model_Condition_Combine {
    
    public function getMatchedEvents() {
        return array();
    }

    public function getDefaultOperatorInputByType() {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
            $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
        }
        return $this->_defaultOperatorInputByType;
    }

    public function loadArray($arr, $key = 'conditions') {
        if (isset($arr['operator'])) {
            $this->setOperator($arr['operator']);
        }

        if (isset($arr['attribute'])) {
            $this->setAttribute($arr['attribute']);
        }

        return parent::loadArray($arr, $key);
    }

    public function getResource() {
        return Mage::getResourceSingleton('evogue_customersegment/segment');
    }

    protected function _createCustomerFilter($customer, $fieldName) {
        return "{$fieldName} = root.entity_id";
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();
        $table = $this->getResource()->getTable('customer/entity');
        $select->from($table, array(new Zend_Db_Expr(1)));
        $select->where($this->_createCustomerFilter($customer, 'entity_id'));
        return $select;
    }

    protected function _getRequiredValidation() {
        return ($this->getValue() == 1);
    }

    public function getConditionsSql($customer, $website) {
        $select         = $this->_prepareConditionsSql($customer, $website);
        $required       = $this->_getRequiredValidation();
        $whereFunction  = ($this->getAggregator() == 'all') ? 'where' : 'orWhere';
        $operator       = $required ? '=' : '<>';

        $gotConditions = false;

        foreach ($this->getConditions() as $condition) {
            if ($sql = $condition->getConditionsSql($customer, $website)) {
                $criteriaSql = "(IFNULL(($sql), 0) {$operator} 1)";
                $select->$whereFunction($criteriaSql);
                $gotConditions = true;
            }
        }

        $subfilterMap = $this->_getSubfilterMap();
        if ($subfilterMap) {
            foreach ($this->getConditions() as $condition) {
                $subfilterType = $condition->getSubfilterType();
                if (isset($subfilterMap[$subfilterType])) {
                    $subfilter = $condition->getSubfilterSql($subfilterMap[$subfilterType], $required, $website);
                    if ($subfilter) {
                        $select->$whereFunction($subfilter);
                        $gotConditions = true;
                    }
                }
            }
        }

        if (!$gotConditions) {
            $select->where('1=1');
        }

        return $select;
    }

    protected function _getSubfilterMap() {
        return array();
    }

    protected function _limitByStoreWebsite(Zend_Db_Select $select, $website, $storeIdField) {
        $storeTable = $this->getResource()->getTable('core/store');
        $select->join(array('store'=> $storeTable), $storeIdField.'=store.store_id', array())
            ->where('store.website_id=?', $website);
        return $this;
    }
}
