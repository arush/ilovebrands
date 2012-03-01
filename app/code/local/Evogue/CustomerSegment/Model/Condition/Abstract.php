<?php
class Evogue_CustomerSegment_Model_Condition_Abstract extends Mage_Rule_Model_Condition_Abstract {
    public function getMatchedEvents() {
        return array();
    }

    public function getDefaultOperatorInputByType() {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
            $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
            $this->_defaultOperatorInputByType['multiselect'] = array('==', '!=', '[]', '![]');

        }
        return $this->_defaultOperatorInputByType;
    }

    public function getDefaultOperatorOptions() {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = parent::getDefaultOperatorOptions();

            $this->_defaultOperatorOptions['[]'] = Mage::helper('rule')->__('contains');
            $this->_defaultOperatorOptions['![]'] = Mage::helper('rule')->__('does not contains');
        }
        return $this->_defaultOperatorOptions;
    }

    public function getResource() {
        return Mage::getResourceSingleton('evogue_customersegment/segment');
    }

    protected function _createCustomerFilter($customer, $fieldName) {
        return "{$fieldName} = root.entity_id";
    }

    protected function _limitByStoreWebsite(Zend_Db_Select $select, $website, $storeIdField)
    {
        $storeTable = $this->getResource()->getTable('core/store');
        $select->join(array('store'=> $storeTable), $storeIdField.'=store.store_id', array())
            ->where('store.website_id=?', $website);
        return $this;
    }
}
