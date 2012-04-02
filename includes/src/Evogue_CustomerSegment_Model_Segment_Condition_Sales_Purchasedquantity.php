<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Sales_Purchasedquantity
    extends Evogue_CustomerSegment_Model_Segment_Condition_Sales_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_sales_purchasedquantity');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('%s Purchased Quantity %s %s while %s of these Conditions match:',
                $this->getAttributeElementHtml(), $this->getOperatorElementHtml(), $this->getValueElementHtml(),
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();

        $operator = $this->getResource()->getSqlOperator($this->getOperator());
        if ($this->getAttribute() == 'total') {
            $result = "IF (SUM(order.total_qty_ordered) {$operator} {$this->getValue()}, 1, 0)";
        } else {
            $result = "IF (AVG(order.total_qty_ordered) {$operator} {$this->getValue()}, 1, 0)";
        }

        $select->from(
            array('order' => $this->getResource()->getTable('sales/order')),
            array(new Zend_Db_Expr($result))
        );
        $this->_limitByStoreWebsite($select, $website, 'order.store_id');
        $select->where($this->_createCustomerFilter($customer, 'order.customer_id'));

        return $select;
    }

    public function loadValueOptions() {
        $this->setValueOption(array());
        return $this;
    }
}
