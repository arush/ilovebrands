<?php
class Evogue_PageCache_Model_Container_Sidebar_Recentlycompared extends Evogue_PageCache_Model_Container_Abstract {

    protected function _getIdentifier() {
        return $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_RECENTLY_COMPARED, '')
            . $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    protected function _getCacheId() {
        return 'CONTAINER_RECENTLYCOMPARED_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);

        return $block->toHtml();
    }
}
