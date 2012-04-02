<?php
class Evogue_TargetRule_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_PATH_TARGETRULE_CONFIG    = 'catalog/evogue_targetrule/';

    const MAX_PRODUCT_LIST_RESULT       = 20;

    public function getMaximumNumberOfProduct($type) {
        switch ($type) {
            case Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'related_position_limit');
                break;
            case Evogue_TargetRule_Model_Rule::UP_SELLS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'upsell_position_limit');
                break;
            case Evogue_TargetRule_Model_Rule::CROSS_SELLS:
                $number = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'crosssell_position_limit');
                break;
            default:
                Mage::throwException(Mage::helper('evogue_targetrule')->__('Invalid product list type'));
        }

        return $this->getMaxProductsListResult($number);
    }

    public function getShowProducts($type) {
        switch ($type) {
            case Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'related_position_behavior');
                break;
            case Evogue_TargetRule_Model_Rule::UP_SELLS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'upsell_position_behavior');
                break;
            case Evogue_TargetRule_Model_Rule::CROSS_SELLS:
                $show = Mage::getStoreConfig(self::XML_PATH_TARGETRULE_CONFIG . 'crosssell_position_behavior');
                break;
            default:
                Mage::throwException(Mage::helper('evogue_targetrule')->__('Invalid product list type'));
        }

        return $show;
    }

    public function getMaxProductsListResult($number = 0) {
        if ($number == 0 || $number > self::MAX_PRODUCT_LIST_RESULT) {
            $number = self::MAX_PRODUCT_LIST_RESULT;
        }

        return $number;
    }
}
