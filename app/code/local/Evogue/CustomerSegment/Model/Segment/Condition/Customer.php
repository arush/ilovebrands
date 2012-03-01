<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        $conditions = array();
        $prefix = 'evogue_customersegment/segment_condition_customer_';
        $conditions = Mage::getModel($prefix.'attributes')->getNewChildSelectOptions();
        $conditions = array_merge($conditions, Mage::getModel($prefix.'newsletter')->getNewChildSelectOptions());
        $conditions = array_merge($conditions, Mage::getModel($prefix.'storecredit')->getNewChildSelectOptions());
        return array(
            'value' => $conditions,
            'label'=>Mage::helper('evogue_customersegment')->__('Customer')
        );
    }
}
