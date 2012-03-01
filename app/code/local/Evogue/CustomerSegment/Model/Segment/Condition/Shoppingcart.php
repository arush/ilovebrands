<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Shoppingcart
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_shoppingcart');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        $prefix = 'evogue_customersegment/segment_condition_shoppingcart_';
        return array('value' => array(
                Mage::getModel($prefix.'amount')->getNewChildSelectOptions(),
                Mage::getModel($prefix.'itemsquantity')->getNewChildSelectOptions(),
                Mage::getModel($prefix.'productsquantity')->getNewChildSelectOptions(),
            ),
            'label' => Mage::helper('evogue_customersegment')->__('Shopping Cart')
        );
    }
}
