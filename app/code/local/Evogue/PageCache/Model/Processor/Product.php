<?php
class Evogue_PageCache_Model_Processor_Product extends Evogue_PageCache_Model_Processor_Default {

    public function prepareContent(Zend_Controller_Response_Http $response) {

        $processor = Mage::getSingleton('evogue_pagecache/processor');
        $countLimit = Mage::getStoreConfig(Mage_Reports_Block_Product_Viewed::XML_PATH_RECENTLY_VIEWED_COUNT);

        $cacheId = $processor->getRecentlyViewedCountCacheId();
        if (!Mage::app()->getCache()->test($cacheId)) {
            Mage::app()->saveCache($countLimit, $cacheId);
        }

        $product = Mage::registry('current_product');
        if ($product) {
            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
            Mage::app()->saveCache($product->getId(), $cacheId);
            Evogue_PageCache_Model_Cookie::registerViewedProducts($product->getId(), $countLimit);
        }
        return parent::prepareContent($response);
    }
}
