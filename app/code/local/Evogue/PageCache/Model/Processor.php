<?php
class Evogue_PageCache_Model_Processor {
    const NO_CACHE_COOKIE               = 'NO_CACHE';
    const XML_NODE_ALLOWED_CACHE        = 'frontend/cache/requests';
    const XML_PATH_ALLOWED_DEPTH        = 'system/page_cache/allowed_depth';
    const XML_PATH_LIFE_TIME            = 'system/page_cache/lifetime';  /** @deprecated after 1.8 */
    const XML_PATH_CACHE_MULTICURRENCY  = 'system/page_cache/multicurrency';
    const XML_PATH_CACHE_DEBUG          = 'system/page_cache/debug';
    const REQUEST_ID_PREFIX             = 'REQEST_';
    const CACHE_TAG                     = 'FPC';  // Full Page Cache, minimize

    const LAST_PRODUCT_COOKIE           = 'LAST_PRODUCT';

    const METADATA_CACHE_SUFFIX        = '_metadata';

    protected $_requestId;

    protected $_requestCacheId;

    protected $_requestTags;

    protected $_metaData = null;

    public function __construct() {
        $uri = $this->_getFullPageUrl();

        $pieces = explode('?', $uri);
        $uri = array_shift($pieces);

        if ($uri) {
            if (isset($_COOKIE['store'])) {
                $uri = $uri.'_'.$_COOKIE['store'];
            }
            if (isset($_COOKIE['currency'])) {
                $uri = $uri.'_'.$_COOKIE['currency'];
            }
            if (isset($_COOKIE[Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP])) {
                $uri .= '_' . $_COOKIE[Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP];
            }
        }
        $this->_requestId       = $uri;
        $this->_requestCacheId  = $this->prepareCacheId($this->_requestId);
        $this->_requestTags     = array(self::CACHE_TAG);
    }

    public function prepareCacheId($id) {
        return self::REQUEST_ID_PREFIX . md5($id);
    }

    public function getRequestId() {
        return $this->_requestId;
    }

    public function getRequestCacheId() {
        return $this->_requestCacheId;
    }

    public function isAllowed() {
        if (!$this->_requestId) {
            return false;
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return false;
        }
        if (isset($_COOKIE['NO_CACHE'])) {
            return false;
        }
        if (isset($_GET['no_cache'])) {
            return false;
        }

        return true;
    }

    public function extractContent($content) {
        Mage::app()->getRequest();

        if (!$content && $this->isAllowed()) {

            $subprocessorClass = $this->getMetadata('cache_subprocessor');
            if (!$subprocessorClass) {
                return $content;
            }

            $subprocessor = new $subprocessorClass;
            $cacheId = $this->prepareCacheId($subprocessor->getPageIdWithoutApp($this));

            $content = Mage::app()->loadCache($cacheId);

            if ($content) {
                if (function_exists('gzuncompress')) {
                    $content = gzuncompress($content);
                }
                $content = $this->_processContent($content);

                // renew recently viewed products
                $productId = Mage::app()->loadCache($this->getRequestCacheId() . '_current_product_id');
                $countLimit = Mage::app()->loadCache($this->getRecentlyViewedCountCacheId());
                if ($productId && $countLimit) {
                    Evogue_PageCache_Model_Cookie::registerViewedProducts($productId, $countLimit);
                }
            }

        }
        return $content;
    }

    public function getRecentlyViewedCountCacheId() {
        $cookieName = Mage_Core_Model_Store::COOKIE_NAME;
        return 'recently_viewed_count' . (isset($_COOKIE[$cookieName]) ? '_' . $_COOKIE[$cookieName] : '');
    }

    public function getSessionInfoCacheId() {
        $cookieName = Mage_Core_Model_Store::COOKIE_NAME;
        return 'full_page_cache_session_info' . (isset($_COOKIE[$cookieName]) ? '_' . $_COOKIE[$cookieName] : '');
    }

    protected function _processContent($content) {
        $placeholders = array();
        preg_match_all(
            Evogue_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content, $placeholders, PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        $containers   = array();
        foreach ($placeholders as $definition) {
            $placeholder= new Evogue_PageCache_Model_Container_Placeholder($definition);
            $container  = $placeholder->getContainerClass();
            if (!$container) {
                continue;
            }
            $container  = new $container($placeholder);
            if (!$container->applyWithoutApp($content)) {
                $containers[] = $container;
            }
        }
        $isProcessed = empty($containers);
        // renew session cookie
        $sessionInfo = Mage::app()->loadCache($this->getSessionInfoCacheId());
        if ($sessionInfo) {
            $sessionInfo = unserialize($sessionInfo);
            foreach ($sessionInfo as $cookieName => $cookieInfo) {
                if (isset($_COOKIE[$cookieName]) && isset($cookieInfo['lifetime'])
                    && isset($cookieInfo['path']) && isset($cookieInfo['domain'])
                    && isset($cookieInfo['secure']) && isset($cookieInfo['httponly'])
                ) {
                    $lifeTime = (0 == $cookieInfo['lifetime']) ? 0 : time() + $cookieInfo['lifetime'];
                    setcookie($cookieName, $_COOKIE[$cookieName], $lifeTime,
                        $cookieInfo['path'], $cookieInfo['domain'],
                        $cookieInfo['secure'], $cookieInfo['httponly']
                    );
                }
            }
        } else {
            $isProcessed = false;
        }

        $sidCookieName = $this->getMetadata('sid_cookie_name');
        $sidCookieValue = ($sidCookieName && isset($_COOKIE[$sidCookieName]) ? $_COOKIE[$sidCookieName] : '');
        Evogue_PageCache_Helper_Url::restoreSid($content, $sidCookieValue);

        if ($isProcessed) {
            return $content;
        } else {
            Mage::register('cached_page_content', $content);
            Mage::register('cached_page_containers', $containers);
            Mage::app()->getRequest()
                ->setModuleName('pagecache')
                ->setControllerName('request')
                ->setActionName('process')
                ->isStraight(true);

            $routingInfo = array(
                    'aliases'              => $this->getMetadata('routing_aliases'),
                    'requested_route'      => $this->getMetadata('routing_requested_route'),
                    'requested_controller' => $this->getMetadata('routing_requested_controller'),
                    'requested_action'     => $this->getMetadata('routing_requested_action')
                );

            Mage::app()->getRequest()->setRoutingInfo($routingInfo);
            return false;
        }
    }

    public function addRequestTag($tag) {
        if (is_array($tag)) {
            $this->_requestTags = array_merge($this->_requestTags, $tag);
        } else {
            $this->_requestTags[] = $tag;
        }
        return $this;
    }

    public function getRequestTags() {
        return $this->_requestTags;
    }

    public function processRequestResponse(Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response) {
        if ($this->canProcessRequest($request)) {
            $processor = $this->getRequestProcessor($request);
            if ($processor && $processor->allowCache($request)) {
                $this->setMetadata('cache_subprocessor', get_class($processor));

                $cacheId = $this->prepareCacheId($processor->getPageIdInApp($this));
                $content = $processor->prepareContent($response);

                Evogue_PageCache_Helper_Url::replaceSid($content);

                if (function_exists('gzcompress')) {
                    $content = gzcompress($content);
                }

                Mage::app()->saveCache($content, $cacheId, $this->getRequestTags());
                $this->setMetadata('routing_aliases', Mage::app()->getRequest()->getAliases());
                $this->setMetadata('routing_requested_route', Mage::app()->getRequest()->getRequestedRouteName());
                $this->setMetadata('routing_requested_controller',
                    Mage::app()->getRequest()->getRequestedControllerName());
                $this->setMetadata('routing_requested_action', Mage::app()->getRequest()->getRequestedActionName());

                $this->setMetadata('sid_cookie_name', Mage::getSingleton('core/session')->getSessionName());

                $this->_saveMetadata();
            }
        }
        return $this;
    }

    public function canProcessRequest(Zend_Controller_Request_Http $request) {
        $res = $this->isAllowed();
        $res = $res && Mage::app()->useCache('full_page');
        if ($request->getParam('no_cache')) {
            $res = false;
        }

        if ($res) {
            $maxDepth = Mage::getStoreConfig(self::XML_PATH_ALLOWED_DEPTH);
            $queryParams = $request->getQuery();
            $res = count($queryParams)<=$maxDepth;
        }
        if ($res) {
            $multicurrency = Mage::getStoreConfig(self::XML_PATH_CACHE_MULTICURRENCY);
            if (!$multicurrency && !empty($_COOKIE['currency'])) {
                $res = false;
            }
        }
        return $res;
    }

    public function getRequestProcessor(Zend_Controller_Request_Http $request) {
        $processor = false;
        $configuration = Mage::getConfig()->getNode(self::XML_NODE_ALLOWED_CACHE);
        if ($configuration) {
            $configuration = $configuration->asArray();
        }
        $module = $request->getModuleName();
        if (isset($configuration[$module])) {
            $model = $configuration[$module];
            $controller = $request->getControllerName();
            if (is_array($configuration[$module]) && isset($configuration[$module][$controller])) {
                $model = $configuration[$module][$controller];
                $action = $request->getActionName();
                if (is_array($configuration[$module][$controller]) && isset($configuration[$module][$controller][$action])) {
                    $model = $configuration[$module][$controller][$action];
                }
            }
            if (is_string($model)) {
                $processor = Mage::getModel($model);
            }
        }
        return $processor;
    }

    public function setMetadata($key, $value) {
        $this->_loadMetadata();
        $this->_metaData[$key] = $value;
        return $this;
    }

    public function getMetadata($key) {
        $this->_loadMetadata();
        return (isset($this->_metaData[$key])) ? $this->_metaData[$key] : null;
    }

    protected function _getFullPageUrl() {
        $uri = false;

        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $_SERVER['SERVER_NAME'];
        }

        if ($uri) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri.= $_SERVER['REQUEST_URI'];
            } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
                $uri.= $_SERVER['UNENCODED_URL'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri.= $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $uri.= $_SERVER['QUERY_STRING'];
                }
            }
        }
        return $uri;
    }

    protected function _saveMetadata() {
        Mage::app()->saveCache(
            serialize($this->_metaData),
            $this->getRequestCacheId() . self::METADATA_CACHE_SUFFIX,
            $this->getRequestTags()
            );
    }

    protected function _loadMetadata() {
        if ($this->_metaData === null) {
            $cacheMetadata = Mage::app()->loadCache($this->getRequestCacheId() . self::METADATA_CACHE_SUFFIX);
            if ($cacheMetadata) {
                $cacheMetadata = unserialize($cacheMetadata);
            }
            $this->_metaData = (empty($cacheMetadata) || !is_array($cacheMetadata)) ? array() : $cacheMetadata;
        }
    }
}