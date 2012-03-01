<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Shoppingcart_Amount
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    protected $_inputType = 'numeric';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_shoppingcart_amount');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_quote_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array('value' => $this->getType(),
            'label'=>Mage::helper('evogue_customersegment')->__('Shopping Cart Total'));
    }

    public function loadAttributeOptions() {
        $this->setAttributeOption(array(
            'subtotal'  => Mage::helper('evogue_customersegment')->__('Subtotal'),
            'grand_total'  => Mage::helper('evogue_customersegment')->__('Grand Total'),
            'tax'  => Mage::helper('evogue_customersegment')->__('Tax'),
            'shipping'  => Mage::helper('evogue_customersegment')->__('Shipping'),
            'store_credit'  => Mage::helper('evogue_customersegment')->__('Store Credit'),
            'gift_card'  => Mage::helper('evogue_customersegment')->__('Gift Card'),
        ));
        return $this;
    }

    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Shopping Cart %s Amount %s %s:',
                $this->getAttributeElementHtml(), $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer, $website)
    {
        $table = $this->getResource()->getTable('sales/quote');
        $addressTable = $this->getResource()->getTable('sales/quote_address');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from(array('quote'=>$table), array(new Zend_Db_Expr(1)))
            ->where('quote.is_active=1')
            ->limit(1);
        $this->_limitByStoreWebsite($select, $website, 'quote.store_id');

        $joinAddress = false;
        switch ($this->getAttribute()) {
            case 'subtotal':
                $field = 'quote.base_subtotal';
                break;
            case 'grand_total':
                $field = 'quote.base_grand_total';
                break;
            case 'tax':
                $field = 'base_tax_amount';
                $joinAddress = true;
                break;
            case 'shipping':
                $field = 'base_shipping_amount';
                $joinAddress = true;
                break;
            case 'store_credit':
                $field = 'quote.base_customer_balance_amount_used';
                break;
            case 'gift_card':
                $field = 'quote.base_gift_cards_amount_used';
                break;
            default:
                Mage::throwException(Mage::helper('evogue_customersegment')->__('Unknown quote total specified.'));
        }

        if ($joinAddress) {
            $subselect = $this->getResource()->createSelect();
            $subselect->from(
                array('address'=>$addressTable),
                array(
                    'quote_id' => 'quote_id',
                    $field     => new Zend_Db_Expr("SUM({$field})")
                )
            );

            $subselect->group('quote_id');
            $select->joinInner(array('address'=>$subselect), 'address.quote_id = quote.entity_id', array());
            $field = "address.{$field}";
        }

        $select->where("{$field} {$operator} ?", $this->getValue());
        $select->where($this->_createCustomerFilter($customer, 'customer_id'));
        return $select;
    }
}
