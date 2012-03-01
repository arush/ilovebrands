<?php
class Evogue_CatalogEvent_Block_Catalog_Category_Event extends Evogue_CatalogEvent_Block_Event_Abstract {

    public function getEvent() {
        return $this->getCategory()->getEvent();
    }

    public function getCategory() {
        return Mage::registry('current_category');
    }

    public function getCategoryUrl($category = null) {
        if ($category === null) {
            $category = $this->getCategory();
        }

        return $category->getUrl();
    }

    public function canDisplay() {
        return Mage::helper('evogue_catalogevent')->isEnabled() &&
               $this->getEvent() &&
               $this->getEvent()->canDisplayCategoryPage();
    }
}
