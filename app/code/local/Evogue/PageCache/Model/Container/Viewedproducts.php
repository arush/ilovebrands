<?php
class Evogue_PageCache_Model_Container_Viewedproducts extends Evogue_PageCache_Model_Container_Abstract {
    const COOKIE_NAME = 'VIEWED_PRODUCT_IDS';

    protected function _getProductIds() {
        $result = $this->_getCookieValue(self::COOKIE_NAME, array());
        if ($result) {
            $result = explode(',', $result);
        }
        return $result;
    }

    protected function _getCacheId() {
        $cacheId = $this->_placeholder->getAttribute('cache_id');
        $productIds = $this->_getProductIds();
        if ($cacheId && $productIds) {
            sort($productIds);
            $cacheId = 'CONTAINER_' . md5($cacheId . implode('_', $productIds));
            return $cacheId;
        }
        return false;
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $productIds = $this->_getProductIds();

        $block = new $block;
        $block->setTemplate($template);
        $block->setProductIds($productIds);

        return $block->toHtml();
    }

    protected function _registerProductsView($productIds) {
        return $this;
    }
}
