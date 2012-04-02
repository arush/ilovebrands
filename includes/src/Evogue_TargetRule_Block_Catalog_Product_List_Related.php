<?php
class Evogue_TargetRule_Block_Catalog_Product_List_Related
    extends Evogue_TargetRule_Block_Catalog_Product_List_Abstract {

    public function getType() {
        return Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS;
    }

    public function getExcludeProductIds() {
        if (is_null($this->_excludeProductIds)) {
            $cartProductIds = Mage::getSingleton('checkout/cart')->getProductIds();
            $this->_excludeProductIds = array_merge($cartProductIds, array($this->getProduct()->getEntityId()));
        }
        return $this->_excludeProductIds;
    }
}
