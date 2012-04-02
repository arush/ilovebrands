<?php
class Evogue_TargetRule_Block_Checkout_Cart_Crosssell extends Mage_Catalog_Block_Product_Abstract {

    protected $_items;

    protected $_products;

    protected $_lastAddedProduct;

    public function getLastAddedProductId() {
        return Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
    }

    public function getLastAddedProduct() {
        if (is_null($this->_lastAddedProduct)) {
            $productId = $this->getLastAddedProductId();
            if ($productId) {
                $this->_lastAddedProduct = Mage::getModel('catalog/product')
                    ->load($productId);
            } else {
                $this->_lastAddedProduct = false;
            }
        }
        return $this->_lastAddedProduct;
    }

    public function getQuote() {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    protected function _getCartProducts() {
        if (is_null($this->_products)) {
            $this->_products = array();
            foreach ($this->getQuote()->getAllItems() as $quoteItem) {
                $product = $quoteItem->getProduct();
                $this->_products[$product->getEntityId()] = $product;
            }
        }

        return $this->_products;
    }

    protected function _getCartProductIds() {
        $products = $this->_getCartProducts();
        return array_keys($products);
    }

    public function getTargetRuleHelper() {
        return Mage::helper('evogue_targetrule');
    }

    protected function _getTargetRuleIndex() {
        if (is_null($this->_index)) {
            $this->_index = Mage::getModel('evogue_targetrule/index');
        }
        return $this->_index;
    }

    public function getPositionLimit() {
        return $this->getTargetRuleHelper()->getMaximumNumberOfProduct(Evogue_TargetRule_Model_Rule::CROSS_SELLS);
    }

    public function getPositionBehavior() {
        return $this->getTargetRuleHelper()->getShowProducts(Evogue_TargetRule_Model_Rule::CROSS_SELLS);
    }

    protected function _getLinkCollection() {
        $collection = Mage::getModel('catalog/product_link')
            ->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setGroupBy();
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($collection);

        Mage::getSingleton('cataloginventory/stock_status')
            ->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    public function getRuleBasedBehaviorPositions() {
        return array(
            Evogue_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
            Evogue_TargetRule_Model_Rule::RULE_BASED_ONLY,
        );
    }

    public function getSelectedBehaviorPositions() {
        return array(
            Evogue_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
            Evogue_TargetRule_Model_Rule::SELECTED_ONLY,
        );
    }

    protected function _getProductsByLastAddedProduct() {
        $product = $this->getLastAddedProduct();
        if (!$product) {
            return array();
        }

        $excludeProductIds = $this->_getCartProductIds();

        $items = array();
        $limit = $this->getPositionLimit();

        if (in_array($this->getPositionBehavior(), $this->getSelectedBehaviorPositions())) {
            $collection = $this->_getLinkCollection()
                ->addProductFilter($product->getEntityId())
                ->addExcludeProductFilter($excludeProductIds)
                ->setPageSize($limit);

            foreach ($collection as $item) {
                $items[$item->getEntityId()] = $item;
            }
        }

        $count = $limit - count($items);
        if (in_array($this->getPositionBehavior(), $this->getRuleBasedBehaviorPositions()) && $count > 0) {
            $excludeProductIds = array_merge(array_keys($items), $excludeProductIds);

            $productIds = $this->_getProductIdsFromIndexByProduct($product, $count, $excludeProductIds);
            if ($productIds) {
                $collection = $this->_getProductCollectionByIds($productIds);
                foreach ($collection as $item) {
                    $items[$item->getEntityId()] = $item;
                }
            }
        }

        return $items;
    }

    protected function _getProductIdsFromIndexByProduct($product, $count, $excludeProductIds = array()) {
        return $this->_getTargetRuleIndex()
            ->setType(Evogue_TargetRule_Model_Rule::CROSS_SELLS)
            ->setLimit($count)
            ->setProduct($product)
            ->setExcludeProductIds($excludeProductIds)
            ->getProductIds();
    }

    protected function _getProductCollectionByIds($productIds) {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        Mage::getSingleton('cataloginventory/stock_status')
            ->addIsInStockFilterToCollection($collection);

        return $collection;
    }

    protected function _getProductIdsFromIndexForCartProducts($limit, $excludeProductIds = array()) {
        $resultIds = array();

        foreach ($this->_getCartProducts() as $product) {
            if ($product->getEntityId() == $this->getLastAddedProductId()) {
                continue;
            }

            $productIds = $this
                ->_getProductIdsFromIndexByProduct($product, $this->getPositionLimit(), $excludeProductIds);
            $resultIds = array_merge($resultIds, $productIds);
        }

        $resultIds = array_unique($resultIds);
        shuffle($resultIds);

        return array_slice($resultIds, 0, $limit);
    }

    public function getItemCollection() {
        if (is_null($this->_items)) {

            $this->_items = $this->_getProductsByLastAddedProduct();

            $limit = $this->getPositionLimit();
            $count = $limit - count($this->_items);
            if ($count > 0) {
                $excludeProductIds = array_merge(array_keys($this->_items), $this->_getCartProductIds());

                if (in_array($this->getPositionBehavior(), $this->getSelectedBehaviorPositions())) {
                    $collection = $this->_getLinkCollection()
                        ->addProductFilter($this->_getCartProductIds())
                        ->addExcludeProductFilter($excludeProductIds)
                        ->setPositionOrder()
                        ->setPageSize($count);

                    foreach ($collection as $product) {
                        $this->_items[$product->getEntityId()] = $product;
                        $excludeProductIds[] = $product->getEntityId();
                    }
                }

                $count = $limit - count($this->_items);
                if (in_array($this->getPositionBehavior(), $this->getRuleBasedBehaviorPositions()) && $count > 0) {
                    $productIds = $this->_getProductIdsFromIndexForCartProducts($count, $excludeProductIds);
                    if ($productIds) {
                        $collection = $this->_getProductCollectionByIds($productIds);
                        foreach ($collection as $product) {
                            $this->_items[$product->getEntityId()] = $product;
                        }
                    }
                }
            }
        }
        return $this->_items;
    }

    public function hasItems() {
        return $this->getItemsCount() > 0;
    }

    public function getItemsCount() {
        return count($this->getItemCollection());
    }
}
