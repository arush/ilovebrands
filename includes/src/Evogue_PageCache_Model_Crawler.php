<?php
class Evogue_PageCache_Model_Crawler extends Mage_Core_Model_Abstract {
    const XML_PATH_CRAWLER_ENABLED     = 'system/page_crawl/enable';
    const XML_PATH_CRAWLER_THREADS     = 'system/page_crawl/threads';
    const XML_PATH_CRAWL_MULTICURRENCY = 'system/page_crawl/multicurrency';


    const USER_AGENT = 'MagentoCrawler';

    protected function _construct() {
        $this->_init('enterprise_pagecache/crawler');
    }

    public function getUrls($pageContent) {
        $urls = array();
        preg_match_all(
            "/\s+href\s*=\s*[\"\']?([^\s\"\']+)[\"\'\s]+/ims",
            $pageContent,
            $urls
        );
        $urls = $urls[1];
        return $urls;
    }

    public function getStoresInfo() {
        $baseUrls = array();

        foreach (Mage::app()->getStores() as $store) {
            $website = Mage::app()->getWebsite($store->getWebsiteId());
            $defaultWebsiteStore = $website->getDefaultStore();
            $defaultWebsiteBaseUrl      = $defaultWebsiteStore->getBaseUrl();
            $defaultWebsiteBaseCurrency = $defaultWebsiteStore->getDefaultCurrencyCode();

            $baseUrl            = Mage::app()->getStore($store)->getBaseUrl();
            $defaultCurrency    = Mage::app()->getStore($store)->getDefaultCurrencyCode();

            $cookie = '';
            if (($baseUrl == $defaultWebsiteBaseUrl) && ($defaultWebsiteStore->getId() != $store->getId())) {
                $cookie = 'store='.$store->getCode().';';
            }

            $baseUrls[] = array(
                'store_id' => $store->getId(),
                'base_url' => $baseUrl,
                'cookie'   => $cookie,
            );
            if ($store->getConfig(self::XML_PATH_CRAWL_MULTICURRENCY)
                && $store->getConfig(Evogue_PageCache_Model_Processor::XML_PATH_CACHE_MULTICURRENCY)) {
                $currencies = $store->getAvailableCurrencyCodes(true);
                foreach ($currencies as $currencyCode) {
                    if ($currencyCode != $defaultCurrency) {
                        $baseUrls[] = array(
                            'store_id' => $store->getId(),
                            'base_url' => $baseUrl,
                            'cookie'   => $cookie.'currency='.$currencyCode.';'
                        );
                    }
                }
            }
        }
        return $baseUrls;
    }

    public function crawl() {
        $storesInfo = $this->getStoresInfo();
        $adapter = new Varien_Http_Adapter_Curl();

        foreach ($storesInfo as $info) {
            $options    = array(CURLOPT_USERAGENT => self::USER_AGENT);
            $storeId    = $info['store_id'];

            if (!Mage::app()->getStore($storeId)->getConfig(self::XML_PATH_CRAWLER_ENABLED)) {
                continue;
            }
            $threads = (int)Mage::app()->getStore($storeId)->getConfig(self::XML_PATH_CRAWLER_THREADS);
            if (!$threads) {
                $threads = 1;
            }
            $stmt       = $this->_getResource()->getUrlStmt($storeId);
            $baseUrl    = $info['base_url'];
            if (!empty($info['cookie'])) {
                $options[CURLOPT_COOKIE] = $info['cookie'];
            }
            $urls = array();
            $urlsCount = 0;
            $totalCount = 0;
            while ($row = $stmt->fetch()) {
                $urls[] = $baseUrl.$row['request_path'];
                $urlsCount++;
                $totalCount++;
                if ($urlsCount==$threads) {
                    $adapter->multiRequest($urls, $options);
                    $urlsCount = 0;
                    $urls = array();
                }
            }
            if (!empty($urls)) {
                $adapter->multiRequest($urls, $options);
            }
        }
        return $this;
    }
}
