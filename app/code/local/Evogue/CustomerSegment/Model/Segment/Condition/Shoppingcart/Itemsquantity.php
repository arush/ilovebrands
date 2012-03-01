<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Shoppingcart_Itemsquantity
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    protected $_inputType = 'numeric';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_shoppingcart_itemsquantity');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_quote_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array('value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Number of Cart Line Items'));
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Number of Shopping Cart Line Items %s %s:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer, $website) {
        $table = $this->getResource()->getTable('sales/quote');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from(array('quote'=>$table), array(new Zend_Db_Expr(1)))
            ->limit(1);
        $this->_limitByStoreWebsite($select, $website, 'quote.store_id');
        $select->where("quote.items_count {$operator} ?", $this->getValue());
        $select->where($this->_createCustomerFilter($customer, 'quote.customer_id'));

        return $select;
    }
}
