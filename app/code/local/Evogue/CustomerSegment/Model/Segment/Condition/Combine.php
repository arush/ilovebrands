<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Combine
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_combine');
    }

	public function getNewChildSelectOptions() {
        $conditions = array(
            array( // subconditions combo
                'value' => 'evogue_customersegment/segment_condition_combine',
                'label' => Mage::helper('evogue_customersegment')->__('Conditions Combination')),

            array( // customer address combo
                'value' => 'evogue_customersegment/segment_condition_customer_address',
                'label' => Mage::helper('evogue_customersegment')->__('Customer Address')),

            // customer attribute group
            Mage::getModel('evogue_customersegment/segment_condition_customer')->getNewChildSelectOptions(),

            // shopping cart group
            Mage::getModel('evogue_customersegment/segment_condition_shoppingcart')->getNewChildSelectOptions(),

            array('value' => array(
                    array( // product list combo
                        'value' => 'evogue_customersegment/segment_condition_product_combine_list',
                        'label' => Mage::helper('evogue_customersegment')->__('Product List')),
                    array( // product history combo
                        'value' => 'evogue_customersegment/segment_condition_product_combine_history',
                        'label' => Mage::helper('evogue_customersegment')->__('Product History')),
                ),
                'label' => Mage::helper('evogue_customersegment')->__('Products'),
            ),

            // sales group
            Mage::getModel('evogue_customersegment/segment_condition_sales')->getNewChildSelectOptions(),
        );

        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }
}
