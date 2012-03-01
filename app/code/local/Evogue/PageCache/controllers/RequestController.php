<?php
class Evogue_PageCache_RequestController extends Mage_Core_Controller_Front_Action {

    public function processAction() {
        $processor  = Mage::getSingleton('evogue_pagecache/processor');
        $content    = Mage::registry('cached_page_content');
        $containers = Mage::registry('cached_page_containers');
        foreach ($containers as $container) {
            $container->applyInApp($content);
        }
        $this->getResponse()->appendBody($content);
        // save session cookie lifetime info
        $cacheId = $processor->getSessionInfoCacheId();
        $sessionInfo = Mage::app()->loadCache($cacheId);
        if ($sessionInfo) {
            $sessionInfo = unserialize($sessionInfo);
        } else {
            $sessionInfo = array();
        }
        $session = Mage::getSingleton('core/session');
        $cookieName = $session->getSessionName();
        $cookieInfo = array(
            'lifetime' => $session->getCookie()->getLifetime(),
            'path'     => $session->getCookie()->getPath(),
            'domain'   => $session->getCookie()->getDomain(),
            'secure'   => $session->getCookie()->isSecure(),
            'httponly' => $session->getCookie()->getHttponly(),
        );
        if (!isset($sessionInfo[$cookieName]) || $sessionInfo[$cookieName] != $cookieInfo) {
            $sessionInfo[$cookieName] = $cookieInfo;
            // customer cookies have to be refreshed as well as the session cookie
            $sessionInfo[Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER] = $cookieInfo;
            $sessionInfo[Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP] = $cookieInfo;
            $sessionInfo = serialize($sessionInfo);
            Mage::app()->saveCache($sessionInfo, $cacheId, array(Evogue_PageCache_Model_Processor::CACHE_TAG));
            
        }
    }
}
