<?php
class Evogue_CustomerSegment_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isEnabled() {
        return (bool)Mage::getStoreConfig('customer/evogue_customersegment/is_enabled');
    }

    public function getOptionsArray() {
        return array(
            array(
                'label' => '',
                'value' => ''
            ),
            array(
                'label' => Mage::helper('evogue_customersegment')->__('Union'),
                'value' => Evogue_CustomerSegment_Model_Segment::VIEW_MODE_UNION_CODE
            ),
            array(
                'label' => Mage::helper('evogue_customersegment')->__('Intersection'),
                'value' => Evogue_CustomerSegment_Model_Segment::VIEW_MODE_INTERSECT_CODE
            )
        );
    }

    public function getViewModeLabel($code) {
        foreach ($this->getOptionsArray() as $option) {
            if (isset($option['label']) && isset($option['value']) && $option['value'] == $code) {
                return $option['label'];
            }
        }
        return '';
    }
}
