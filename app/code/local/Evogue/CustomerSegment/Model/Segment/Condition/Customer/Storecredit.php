<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Storecredit
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    protected $_inputType = 'numeric';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_storecredit');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('customer_balance_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array(array('value' => $this->getType(),
            'label'=>Mage::helper('evogue_customersegment')->__('Store Credit')));
    }

    public function asHtml() {
        $operator = $this->getOperatorElementHtml();
        $element = $this->getValueElementHtml();
        return $this->getTypeElementHtml()
            .Mage::helper('evogue_customersegment')->__('Customer Store Credit Amount %s %s:', $operator, $element)
            .$this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer, $website) {
        $table = $this->getResource()->getTable('evogue_customerbalance/balance');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from($table, array(new Zend_Db_Expr(1)))
            ->limit(1);
        $select->where($this->_createCustomerFilter($customer, 'customer_id'));
        $select->where('website_id=?', $website);
        $select->where("amount {$operator} ?", $this->getValue());

        return $select;
    }
}
