<?php
class Evogue_PageCache_Model_Container_Accountlinks extends Evogue_PageCache_Model_Container_Customer {

    protected function _isLogged() {
        return ($this->_getCookieValue(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER) ? true : false);
    }

    protected function _getCacheId() {
        return 'CONTAINER_LINKS_' . md5($this->_placeholder->getAttribute('cache_id') .
            (($this->_isLogged()) ? 'logged' : 'not_logged'));
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $name = $this->_placeholder->getAttribute('name');
        $links = $this->_placeholder->getAttribute('links');

        $block = new $block;
        $block->setTemplate($template);
        $block->setNameInLayout($name);

        if ($links) {
            $links = unserialize(base64_decode($links));
            foreach ($links as $position => $linkInfo) {
                $block->addLink($linkInfo['label'], $linkInfo['url'], $linkInfo['title'], false, array(), $position,
                    $linkInfo['li_params'], $linkInfo['a_params'], $linkInfo['before_text'], $linkInfo['after_text']);
            }
        }

        return $block->toHtml();
    }
}
