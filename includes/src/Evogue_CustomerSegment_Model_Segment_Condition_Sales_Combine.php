<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Sales_Combine
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {
    protected $_inputType = 'numeric';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_sales_combine');
    }

    public function getNewChildSelectOptions() {
        return array_merge_recursive(parent::getNewChildSelectOptions(), array(
            Mage::getModel('evogue_customersegment/segment_condition_order_status')->getNewChildSelectOptions(),
            // date ranges
            array(
                'value' => array(
                    Mage::getModel('evogue_customersegment/segment_condition_uptodate')->getNewChildSelectOptions(),
                    Mage::getModel('evogue_customersegment/segment_condition_daterange')->getNewChildSelectOptions(),
                ),
                'label' => Mage::helper('evogue_customersegment')->__('Date Ranges')
            ),
        ));
    }

    public function loadAttributeOptions() {
        $this->setAttributeOption(array(
            'total'   => Mage::helper('evogue_customersegment')->__('Total'),
            'average' => Mage::helper('evogue_customersegment')->__('Average'),
        ));
        return $this;
    }

    public function getValueElementType() {
        return 'text';
    }

    protected function _getRequiredValidation() {
        return true;
    }

    protected function _getSubfilterMap() {
        return array(
            'order' => 'order.status',
            'date' => 'order.created_at',
        );
    }
}
