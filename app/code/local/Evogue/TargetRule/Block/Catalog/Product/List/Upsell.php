<?php
class Evogue_TargetRule_Block_Catalog_Product_List_Upsell
    extends Evogue_TargetRule_Block_Catalog_Product_List_Abstract {

    public function getType() {
        return Evogue_TargetRule_Model_Rule::UP_SELLS;
    }

    public function getLinkCollection() {
        if (is_null($this->_linkCollection)) {
            parent::getLinkCollection();
            Mage::dispatchEvent('catalog_product_upsell', array(
                'product'       => $this->getProduct(),
                'collection'    => $this->_linkCollection,
                'limit'         => $this->getPositionLimit()
            ));
        }

        return $this->_linkCollection;
    }
}
