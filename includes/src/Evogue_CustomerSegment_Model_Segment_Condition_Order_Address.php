<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Order_Address
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {
    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_order_address');
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return Mage::getModel('evogue_customersegment/segment_condition_order_address_combine')
            ->getNewChildSelectOptions();
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('If Order Addresses match %s of these Conditions:',
                $this->getAggregatorElement()->getHtml()) . $this->getRemoveLinkHtml();
    }

    public function getValue() {
        return 1;
    }

    protected function _prepareConditionsSql($customer, $website) {
        $resource = $this->getResource();
        $select = $resource->createSelect();

        $mainAddressTable   = $this->getResource()->getTable('sales/order_address');
        $extraAddressTable  = $this->getResource()->getTable('evogue_customer/sales_order_address');
        $orderTable         = $this->getResource()->getTable('sales/order');

        $select
            ->from(array('order_address' => $mainAddressTable), array(new Zend_Db_Expr(1)))
            ->join(
                array('order_address_order' => $orderTable),
                'order_address.parent_id = order_address_order.entity_id',
                array())
            ->joinLeft(
                array('extra_order_address' => $extraAddressTable),
                'order_address.entity_id = extra_order_address.entity_id',
                array())
            ->where($this->_createCustomerFilter($customer, 'order_address_order.customer_id'))
            ->limit(1);
        $this->_limitByStoreWebsite($select, $website, 'order_address_order.store_id');

        return $select;
    }

    protected function _getSubfilterMap() {
        return array(
            'order_address_type' => 'order_address_type.value',
        );
    }
}
