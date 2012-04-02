<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Sales_Ordersnumber
    extends Evogue_CustomerSegment_Model_Segment_Condition_Sales_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_sales_ordersnumber');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function loadValueOptions() {
        $this->setValueOption(array());
        return $this;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Number of Orders %s %s while %s of these Conditions match:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml(),
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $result = "IF (COUNT(*) {$operator} {$this->getValue()}, 1, 0)";
        $select->from(
            array('order' => $this->getResource()->getTable('sales/order')),
            array(new Zend_Db_Expr($result))
        );
        $this->_limitByStoreWebsite($select, $website, 'order.store_id');
        $select->where($this->_createCustomerFilter($customer, 'order.customer_id'));

        return $select;
    }
}
