<?php
class evogue_PageCache_Model_Container_Sidebar_Cart extends evogue_PageCache_Model_Container_Abstract {

    const CACHE_TAG_PREFIX = 'cartsidebar';

    protected function _getIdentifier() {
        return $this->_getCookieValue(evogue_PageCache_Model_Cookie::COOKIE_CART, '')
            . $this->_getCookieValue(evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    protected function _getCacheId() {
        return 'CONTAINER_SIDEBAR_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }

    public function saveCache($blockContent) {
        $cacheId = $this->_getCacheId();
        if ($cacheId !== false) {
            $cacheTag = md5(self::CACHE_TAG_PREFIX . $this->_getIdentifier());
            $this->_saveCache($blockContent, $cacheId, array($cacheTag));
        }
        return $this;
    }
}
