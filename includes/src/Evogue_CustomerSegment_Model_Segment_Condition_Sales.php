<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Sales
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_sales');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        return array(
            'value' => array(
                array( // order address combo
                    'value' => 'evogue_customersegment/segment_condition_order_address',
                    'label' => Mage::helper('evogue_customersegment')->__('Order Address')),
                array(
                    'value' => 'evogue_customersegment/segment_condition_sales_salesamount',
                    'label' => Mage::helper('evogue_customersegment')->__('Sales Amount')),
                array(
                    'value' => 'evogue_customersegment/segment_condition_sales_ordersnumber',
                    'label' => Mage::helper('evogue_customersegment')->__('Number of Orders')),
                array(
                    'value' => 'evogue_customersegment/segment_condition_sales_purchasedquantity',
                    'label' => Mage::helper('evogue_customersegment')->__('Purchased Quantity')),
             ),
            'label' => Mage::helper('evogue_customersegment')->__('Sales')
        );
    }
}
