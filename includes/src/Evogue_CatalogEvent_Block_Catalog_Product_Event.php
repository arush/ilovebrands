<?php
class Evogue_CatalogEvent_Block_Catalog_Product_Event extends Evogue_CatalogEvent_Block_Event_Abstract {

    public function getEvent() {
        if ($this->getProduct()) {
            return $this->getProduct()->getEvent();
        }

        return false;
    }

    public function getProduct() {
        return Mage::registry('current_product');
    }

    public function canDisplay()
    {
        return Mage::helper('evogue_catalogevent')->isEnabled() &&
               $this->getProduct() &&
               $this->getEvent() &&
               $this->getEvent()->canDisplayProductPage() &&
               !$this->getProduct()->getEventNoTicker();
    }

}
