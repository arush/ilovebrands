<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Order_Address_Combine
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_order_address_combine');
    }

    public function getNewChildSelectOptions() {
        $prefix = 'evogue_customersegment/segment_condition_order_address_';
        $result = array_merge_recursive(parent::getNewChildSelectOptions(), array(
            array(
                'value' => $this->getType(),
                'label' => Mage::helper('evogue_customersegment')->__('Conditions Combination')),
            Mage::getModel($prefix.'type')->getNewChildSelectOptions(),
            Mage::getModel($prefix.'attributes')->getNewChildSelectOptions(),
        ));
        return $result;
    }
}
