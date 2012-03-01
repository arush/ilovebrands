<?php
class Evogue_CustomerBalance_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLED     = 'customer/evogue_customerbalance/is_enabled';
    const XML_PATH_AUTO_REFUND = 'customer/evogue_customerbalance/refund_automatically';

    public function isEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED) == 1;
    }

    public function isAutoRefundEnabled() {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_REFUND);
    }
}
