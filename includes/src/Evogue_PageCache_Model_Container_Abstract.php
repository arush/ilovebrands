<?php
abstract class Evogue_PageCache_Model_Container_Abstract {

    protected $_placeholder;

    public function __construct($placeholder) {
        $this->_placeholder = $placeholder;
    }

    protected function _getCacheId() {
        return false;
    }

    public function applyWithoutApp(&$content) {
        $cacheId = $this->_getCacheId();
        if ($cacheId !== false) {
            $block = $this->_loadCache($cacheId);
            if ($block !== false) {
                $this->_applyToContent($content, $block);
            } else {
                return false;
            }
        } else {
            $this->_applyToContent($content, '');
        }
        return true;
    }

    public function applyInApp(&$content) {
        $blockContent = $this->_renderBlock();
        if ($blockContent !== false) {
            if (Mage::getStoreConfig(Evogue_PageCache_Model_Processor::XML_PATH_CACHE_DEBUG)){
                $debugBlock = new Evogue_PageCache_Block_Debug;
                $debugBlock->setDynamicBlockContent($blockContent);
                $this->_applyToContent($content, $debugBlock->toHtml());
            } else {
                $this->_applyToContent($content, $blockContent);
            }
            $this->saveCache($blockContent);
            return true;
        }
        return false;
    }

    public function saveCache($blockContent) {
        $cacheId = $this->_getCacheId();
        if ($cacheId !== false) {
            $this->_saveCache($blockContent, $cacheId);
        }
        return $this;
    }

    protected function _renderBlock() {
        return false;
    }

    protected function _applyToContent(&$content, $containerContent) {
        $containerContent = $this->_placeholder->getStartTag() . $containerContent . $this->_placeholder->getEndTag();
        $content = str_replace($this->_placeholder->getReplacer(), $containerContent, $content);
    }

    protected function _loadCache($id) {
        return Mage::app()->getCache()->load($id);
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null) {
        $tags[] = Evogue_PageCache_Model_Processor::CACHE_TAG;
        if (is_null($lifetime)) {
            $lifetime = $this->_placeholder->getAttribute('cache_lifetime') ?
                $this->_placeholder->getAttribute('cache_lifetime') : false;
        }

        Evogue_PageCache_Helper_Url::replaceSid($data);

        Mage::app()->getCache()->save($data, $id, $tags, $lifetime);
        return $this;
    }

    protected function _getCookieValue($cookieName, $defaultValue = null) {
        return (array_key_exists($cookieName, $_COOKIE) ? $_COOKIE[$cookieName] : $defaultValue);
    }
}
