<?php
class Evogue_PageCache_Model_Config extends Varien_Simplexml_Config {

    protected $_placeholders = null;

    public function __construct($data = null) {
        parent::__construct($data);
        $this->setCacheId('cache_config');
        $this->_cacheChecksum   = null;
        $this->_cache = Mage::app()->getCache();

        $canUsaCache = Mage::app()->useCache('config');
        if ($canUsaCache) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration('cache.xml');
        $this->setXml($config->getNode());

        if ($canUsaCache) {
            $this->saveCache(array(Mage_Core_Model_Config::CACHE_TAG));
        }
        return $this;
    }

    protected function _initPlaceholders() {
        if ($this->_placeholders === null) {
            $this->_placeholders = array();
            foreach ($this->getNode('placeholders')->children() as $placeholder) {
                $this->_placeholders[(string)$placeholder->block] = array(
                    'container'     => (string)$placeholder->container,
                    'code'          => (string)$placeholder->placeholder,
                    'cache_lifetime'=> (int) $placeholder->cache_lifetime,
                    'name'          => (string) $placeholder->name
                );
            }
        }
        return $this;
    }

    public function getBlockPlaceholder($block) {
        $this->_initPlaceholders();
        $type = $block->getType();
        if (isset($this->_placeholders[$type])) {
            if (!empty($this->_placeholders[$type]['name'])
                && $this->_placeholders[$type]['name'] != $block->getNameInLayout()) {
                return false;
            }
            $placeholder = $this->_placeholders[$type]['code']
                . ' container="'.$this->_placeholders[$type]['container'].'"'
                . ' block="' . get_class($block) . '"';
            $placeholder.= ' cache_id="' . $block->getCacheKey() . '"';
            foreach ($block->getCacheKeyInfo() as $k => $v) {
                if (is_string($k) && !empty($k)) {
                    $placeholder .= ' ' . $k . '="' . $v . '"';
                }
            }
            $placeholder = Mage::getModel('evogue_pagecache/container_placeholder', $placeholder);
            return $placeholder;
        }
        return false;
    }
}
