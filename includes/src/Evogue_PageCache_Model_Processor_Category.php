<?php
class Evogue_PageCache_Model_Processor_Category extends Evogue_PageCache_Model_Processor_Default {
    protected $_paramsMap = array(
        'display_mode'  => 'mode',
        'limit_page'    => 'limit',
        'sort_order'    => 'order',
        'sort_direction'=> 'dir',
    );

    public function getPageIdInApp(Evogue_PageCache_Model_Processor $processor) {
        $this->_prepareCatalogSession();

        $queryParams = array_merge($this->_getSessionParams(), $_GET);
        ksort($queryParams);
        $queryParams = json_encode($queryParams);

        Evogue_PageCache_Model_Cookie::setCategoryCookieValue($queryParams);

        return $processor->getRequestId() . '_' . md5($queryParams);
    }

    public function getPageIdWithoutApp(Evogue_PageCache_Model_Processor $processor)
    {
        $queryParams = $_GET;

        $sessionParams = Evogue_PageCache_Model_Cookie::getCategoryCookieValue();
        if ($sessionParams) {
            $sessionParams = (array)json_decode($sessionParams);
            foreach ($sessionParams as $key => $value) {
                if (in_array($key, $this->_paramsMap) && !isset($queryParams[$key])) {
                    $queryParams[$key] = $value;
                }
            }
        }
        ksort($queryParams);
        $queryParams = json_encode($queryParams);

        Evogue_PageCache_Model_Cookie::setCategoryCookieValue($queryParams);

        return $processor->getRequestId() . '_' . md5($queryParams);
    }

    public function allowCache(Zend_Controller_Request_Http $request) {
        $res = parent::allowCache($request);
        if ($res) {
            $params = $this->_getSessionParams();
            $queryParams = $request->getQuery();
            $queryParams = array_merge($queryParams, $params);
            $maxDepth = Mage::getStoreConfig(Evogue_PageCache_Model_Processor::XML_PATH_ALLOWED_DEPTH);
            $res = count($queryParams)<=$maxDepth;
        }
        return $res;
    }

    protected function _getSessionParams() {
        $params = array();
        $data   = Mage::getSingleton('catalog/session')->getData();
        foreach ($this->_paramsMap as $sessionParam => $queryParam) {
            if (isset($data[$sessionParam])) {
                $params[$queryParam] = $data[$sessionParam];
            }
        }
        return $params;
    }

    protected function _prepareCatalogSession() {
        $sessionParams = Evogue_PageCache_Model_Cookie::getCategoryCookieValue();
        if ($sessionParams) {
            $session = Mage::getSingleton('catalog/session');
            $sessionParams = (array)json_decode($sessionParams);
            foreach ($sessionParams as $key => $value) {
                if (in_array($key, $this->_paramsMap)) {
                    $session->setData($key, $value);
                }
            }
        }
    }
}
