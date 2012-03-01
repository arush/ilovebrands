<?php
class Evogue_PageCache_Model_Container_Wishlist extends Evogue_PageCache_Model_Container_Abstract {

    protected function _getIdentifier() {
        return $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_WISHLIST, '')
            . ($this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP, ''));
    }

    protected function _getCacheId() {
        return 'CONTAINER_WISHLIST_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }
}
