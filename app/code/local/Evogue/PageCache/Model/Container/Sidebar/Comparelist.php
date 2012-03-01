<?php
class Evogue_PageCache_Model_Container_Sidebar_Comparelist extends Evogue_PageCache_Model_Container_Abstract {

    protected function _getIdentifier() {
        return $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_COMPARE_LIST, '');
    }

    protected function _getCacheId() {
        return 'CONTAINER_COMPARELIST_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $template = $this->_placeholder->getAttribute('template');

        $block = Mage::app()->getLayout()->createBlock('catalog/product_compare_list');
        $block->setTemplate($template);

        return $block->toHtml();
    }
}
