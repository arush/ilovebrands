<?php
class Evogue_CatalogEvent_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLED = 'catalog/evogue_catalogevent/enabled';

    public function getEventImageUrl($event) {
        if ($event->getImage()) {
            return $event->getImageUrl();
        }

        return false;
    }

    public function isEnabled() {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }
}
