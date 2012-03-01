<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Product_Combine_History
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {
    const VIEWED    = 'viewed_history';
    const ORDERED   = 'ordered_history';

    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_product_combine_history');
        $this->setValue(self::VIEWED);
    }

    public function getMatchedEvents() {
        $events = array();
        switch ($this->getValue()) {
            case self::ORDERED:
                $events = array('sales_order_save_commit_after');
                break;
            default:
                $events = array('catalog_controller_product_view');
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
            self::VIEWED  => Mage::helper('evogue_customersegment')->__('viewed'),
            self::ORDERED => Mage::helper('evogue_customersegment')->__('ordered'),
        ));
        return $this;
    }

    public function getValueElementType() {
        return 'select';
    }

    public function loadOperatorOptions() {
        parent::loadOperatorOptions();
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('was'),
            '!='  => Mage::helper('rule')->__('was not')
        ));
        return $this;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('If Product %s %s and matches %s of these Conditions:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml(), $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    protected function _prepareConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();

        switch ($this->getValue()) {
            case self::ORDERED:
                $select->from(
                    array('item' => $this->getResource()->getTable('sales/order_item')),
                    array(new Zend_Db_Expr(1))
                );
                $select->joinInner(
                    array('order' => $this->getResource()->getTable('sales/order')),
                    'item.order_id = order.entity_id',
                    array()
                );
                $select->where($this->_createCustomerFilter($customer, 'order.customer_id'));
                $this->_limitByStoreWebsite($select, $website, 'order.store_id');
                break;
            default:
                $select->from(
                    array('item' => $this->getResource()->getTable('reports/viewed_product_index')),
                    array(new Zend_Db_Expr(1))
                );
                $select->where($this->_createCustomerFilter($customer, 'item.customer_id'));
                $this->_limitByStoreWebsite($select, $website, 'item.store_id');
                break;
        }

        $select->limit(1);
        return $select;
    }

    protected function _getRequiredValidation() {
        return ($this->getOperator() == '==');
    }

    protected function _getSubfilterMap() {
        switch ($this->getValue()) {
            case self::ORDERED:
                $dateField = 'item.created_at';
                break;

            default:
                $dateField = 'item.added_at';
                break;
        }

        return array(
            'product' => 'item.product_id',
            'date'    => $dateField
        );
    }
}
