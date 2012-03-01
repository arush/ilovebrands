<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Sales_Salesamount
    extends Evogue_CustomerSegment_Model_Segment_Condition_Sales_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_sales_salesamount');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('%s Sales Amount %s %s while %s of these Conditions match:',
                $this->getAttributeElementHtml(), $this->getOperatorElementHtml(), $this->getValueElementHtml(),
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();

        $operator = $this->getResource()->getSqlOperator($this->getOperator());
        if ($this->getAttribute() == 'total') {
            $result = "IF (IF(SUM(order.base_grand_total), SUM(order.base_grand_total), 0) {$operator} {$this->getValue()}, 1, 0)";
        } else {
            $result = "IF (IF(AVG(order.base_grand_total), AVG(order.base_grand_total), 0) {$operator} {$this->getValue()}, 1, 0)";
        }

        $select->from(
            array('order' => $this->getResource()->getTable('sales/order')),
            array(new Zend_Db_Expr($result))
        );
        $this->_limitByStoreWebsite($select, $website, 'order.store_id');
        $select->where($this->_createCustomerFilter($customer, 'order.customer_id'));

        return $select;
    }
}
