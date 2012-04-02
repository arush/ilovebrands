<?php
class Evogue_PageCache_Model_Processor_Default {

    private $_placeholder;

    protected $_noCacheGetParams = array('___store', '___from_store');

    public function allowCache(Zend_Controller_Request_Http $request) {
        foreach ($this->_noCacheGetParams as $param) {
            if (!is_null($request->getParam($param, null))) {
                return false;
            }
        }
        if (Mage::getSingleton('core/session')->getNoCacheFlag()) {
            return false;
        }
        return true;
    }

    public function prepareContent(Zend_Controller_Response_Http $response) {
        $content = $response->getBody();
        $placeholders = array();
        preg_match_all(
            Evogue_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content,
            $placeholders,
            PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        try {
            foreach ($placeholders as $definition) {
                $this->_placeholder = Mage::getModel('evogue_pagecache/container_placeholder', $definition);
                $content = preg_replace_callback($this->_placeholder->getPattern(), array($this, '_getPlaceholderReplacer'), $content);
            }
            $this->_placeholder = null;
        } catch (Exception $e) {
            $this->_placeholder = null;
            throw $e;
        }
        return $content;
    }

    protected function _getPlaceholderReplacer($matches) {
        $container = $this->_placeholder->getContainerClass();

        if ($container && !Mage::getIsDeveloperMode()) {
            $container = new $container($this->_placeholder);
            $blockContent = $matches[1];
            $container->saveCache($blockContent);
        }
        return $this->_placeholder->getReplacer();
    }

    public function getPageIdInApp(Evogue_PageCache_Model_Processor $processor) {
        return $this->getPageIdWithoutApp($processor);
    }

    public function getPageIdWithoutApp(Evogue_PageCache_Model_Processor $processor) {
        $queryParams = $_GET;
        ksort($queryParams);
        $queryParamsHash = md5(serialize($queryParams));
        return $processor->getRequestId() . '_' . $queryParamsHash;
    }

    public function getRequestUri(Evogue_PageCache_Model_Processor $processor, Zend_Controller_Request_Http $request) {
    }
}
