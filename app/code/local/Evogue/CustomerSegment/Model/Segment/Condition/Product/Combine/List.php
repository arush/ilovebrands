<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Product_Combine_List
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {
    const WISHLIST  = 'wishlist';
    const CART      = 'shopping_cart';

    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_product_combine_list');
        $this->setValue(self::CART);
    }

    public function getMatchedEvents() {
        $events = array();
        switch ($this->getValue()) {
            case self::WISHLIST:
                $events = array('wishlist_items_renewed');
                break;
            default:
                $events = array('checkout_cart_save_after');
        }
        return $events;
    }

    public function getNewChildSelectOptions() {
        return Mage::getModel('evogue_customersegment/segment_condition_product_combine')
            ->setDateConditions(true)
            ->getNewChildSelectOptions();
    }

    public function loadValueOptions() {
        $this->setValueOption(array(
            self::CART      => Mage::helper('evogue_customersegment')->__('Shopping Cart'),
            self::WISHLIST  => Mage::helper('evogue_customersegment')->__('Wishlist'),
        ));
        return $this;
    }

    public function getValueElementType() {
        return 'select';
    }

    public function loadOperatorOptions() {
        parent::loadOperatorOptions();
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('found'),
            '!='  => Mage::helper('rule')->__('not found')
        ));
        return $this;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('If Product is %s in the %s with %s of these Conditions match:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml(), $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();

        switch ($this->getValue()) {
            case self::WISHLIST:
                $select->from(
                    array('item' => $this->getResource()->getTable('wishlist/item')),
                    array(new Zend_Db_Expr(1))
                );
                $conditions = "item.wishlist_id = list.wishlist_id";
                $select->joinInner(
                    array('list' => $this->getResource()->getTable('wishlist/wishlist')),
                    $conditions,
                    array()
                );
                $this->_limitByStoreWebsite($select, $website, 'item.store_id');
                break;
            default:
                $select->from(
                    array('item' => $this->getResource()->getTable('sales/quote_item')),
                    array(new Zend_Db_Expr(1))
                );
                $conditions = "item.quote_id = list.entity_id";
                $select->joinInner(
                    array('list' => $this->getResource()->getTable('sales/quote')),
                    $conditions,
                    array()
                );
                $this->_limitByStoreWebsite($select, $website, 'list.store_id');
                break;
        }

        $select->where($this->_createCustomerFilter($customer, 'list.customer_id'));
        $select->limit(1);

        return $select;
    }

    protected function _getRequiredValidation() {
        return ($this->getOperator() == '==');
    }

    protected function _getSubfilterMap() {
        switch ($this->getValue()) {
            case self::WISHLIST:
                $dateField = 'item.added_at';
                break;

            default:
                $dateField = 'item.created_at';
                break;
        }

        return array(
            'product' => 'item.product_id',
            'date'    => $dateField
        );
    }
}
