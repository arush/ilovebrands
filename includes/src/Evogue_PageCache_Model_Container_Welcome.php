<?php
class Evogue_PageCache_Model_Container_Welcome extends Evogue_PageCache_Model_Container_Customer {

    protected function _getIdentifier() {
        return $this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    protected function _getCacheId() {
        return 'CONTAINER_WELCOME_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        return Mage::app()->getLayout()->createBlock('page/html_header')->getWelcome();
    }
}
