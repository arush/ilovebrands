<?php
class Evogue_PageCache_Model_Container_Messages extends Evogue_PageCache_Model_Container_Abstract {

    protected $_messageStoreTypes = array(
        'core/session',
        'customer/session',
        'catalog/session',
        'checkout/session',
        'tag/session'
    );

    protected function _isNewMessageRecived() {
        return ($this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_MESSAGE) ? true : false);
    }

    public function applyWithoutApp(&$content) {
        if ($this->_isNewMessageRecived()) {
            return false;
        }
        return parent::applyWithoutApp($content);
    }

    protected function _renderBlock() {
        Mage::getSingleton('core/cookie')->delete(Evogue_PageCache_Model_Cookie::COOKIE_MESSAGE);

        $block = $this->_placeholder->getAttribute('block');
        $block = new $block;

        foreach ($this->_messageStoreTypes as $type) {
            $this->_addMessagesToBlock($type, $block);
        }

        return $block->toHtml();
    }

    protected function _addMessagesToBlock($messagesStorage, Mage_Core_Block_Messages $block) {
        if ($storage = Mage::getSingleton($messagesStorage)) {
            $block->addMessages($storage->getMessages(true));
            $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
        }
    }
}
