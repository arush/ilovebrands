<?php
class Evogue_PageCache_Model_Container_Wishlistlinks extends Evogue_PageCache_Model_Container_Abstract {

    protected function _getIdentifier() {
        return $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_WISHLIST_ITEMS, '')
            . $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    protected function _getCacheId() {
        return 'CONTAINER_WISHLINKS_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $block = new $block;
        return $block->toHtml();
    }
}
