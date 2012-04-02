<?php
class Evogue_PageCache_Model_Observer {

    protected $_processor;
    protected $_config;
    protected $_isEnabled;

    public function __construct() {
        $this->_processor = Mage::getSingleton('evogue_pagecache/processor');
        $this->_config    = Mage::getSingleton('evogue_pagecache/config');
        $this->_isEnabled = Mage::app()->useCache('full_page');
    }

    public function isCacheEnabled() {
        return $this->_isEnabled;
    }

    public function cacheResponse(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $frontController = $observer->getEvent()->getFront();
        $request = $frontController->getRequest();
        $response = $frontController->getResponse();
        $this->_processor->processRequestResponse($request, $response);
        return $this;
    }

    public function processPreDispatch(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $action = $observer->getEvent()->getControllerAction();
        $request = $action->getRequest();

        $noCache = $this->_getCookie()->get(Evogue_PageCache_Model_Processor::NO_CACHE_COOKIE);
        if ($noCache) {
            Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(false);
            $this->_getCookie()->renew(Evogue_PageCache_Model_Processor::NO_CACHE_COOKIE);
        } elseif ($action) {
            Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(true);
        }
        if ($this->_processor->canProcessRequest($request)) {
            Mage::app()->getCacheInstance()->banUse(Mage_Core_Block_Abstract::CACHE_GROUP);
        }
        $this->_getCookie()->updateCustomerCookies();
        return $this;
    }

    public function registerModelTag(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $object = $observer->getEvent()->getObject();
        if ($object && $object->getId()) {
            $tags = $object->getCacheIdTags();
            if ($tags) {
                $this->_processor->addRequestTag($tags);
            }
        }
    }

    public function checkCategoryState(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $category = Mage::registry('current_category');

        if ($category && $category->getEvent()) {
            $request = $observer->getEvent()->getControllerAction()->getRequest();
            $request->setParam('no_cache', true);
        }
        return $this;
    }

    public function checkProductState(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $product = Mage::registry('current_product');

        if ($product && $product->getEvent()) {
            $request = $observer->getEvent()->getControllerAction()->getRequest();
            $request->setParam('no_cache', true);
        }
        return $this;
    }

    public function validateDataChanges(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $object = $observer->getEvent()->getObject();
        $object = Mage::getModel('evogue_pagecache/validator')->checkDataChange($object);
    }

    public function validateDataDelete(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $object = $observer->getEvent()->getObject();
        $object = Mage::getModel('evogue_pagecache/validator')->checkDataDelete($object);
    }

    public function cleanCache() {
        Mage::app()->cleanCache(Evogue_PageCache_Model_Processor::CACHE_TAG);
        return $this;
    }

    public function invalidateCache() {
        Mage::app()->getCacheInstance()->invalidateType('full_page');
        return $this;
    }

    public function renderBlockPlaceholder(Varien_Event_Observer $observer) {
        if (!$this->_isEnabled) {
            return $this;
        }
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        $placeholder = $this->_config->getBlockPlaceholder($block);
        if ($transport && $placeholder) {
            $blockHtml = $transport->getHtml();
            $blockHtml = $placeholder->getStartTag() . $blockHtml . $placeholder->getEndTag();
            $transport->setHtml($blockHtml);
        }
        return $this;
    }

    public function blockCreateAfter(Varien_Event_Observer $observer) {
        if (!$this->_isEnabled) {
            return $this;
        }
        $block  = $observer->getEvent()->getBlock();
        $placeholder = $this->_config->getBlockPlaceholder($block);
        if ($placeholder) {
            $block->setFrameTags($placeholder->getStartTag(), $placeholder->getEndTag());
        }
        return $this;
    }

    public function registerQuoteChange(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $quote = ($observer->getEvent()->getQuote()) ? $observer->getEvent()->getQuote() :
            $observer->getEvent()->getQuoteItem()->getQuote();
        $this->_getCookie()->setObscure(Evogue_PageCache_Model_Cookie::COOKIE_CART, 'quote_' . $quote->getId());

        $cacheTag = md5(Evogue_PageCache_Model_Container_Sidebar_Cart::CACHE_TAG_PREFIX
            . $this->_getCookie()->get(Evogue_PageCache_Model_Cookie::COOKIE_CART)
            . $this->_getCookie()->get(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER));
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($cacheTag));

        return $this;
    }

    public function registerCompareListChange(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        $listItems = Mage::helper('catalog/product_compare')->getItemCollection();
        $previouseList = $this->_getCookie()->get(Evogue_PageCache_Model_Cookie::COOKIE_COMPARE_LIST);
        $previouseList = (empty($previouseList)) ? array() : explode(',', $previouseList);

        $ids = array();
        foreach ($listItems as $item) {
            $ids[] = $item->getId();
        }
        sort($ids);
        $this->_getCookie()->set(Evogue_PageCache_Model_Cookie::COOKIE_COMPARE_LIST, implode(',', $ids));

        $recentlyComparedProducts = $this->_getCookie()
            ->get(Evogue_PageCache_Model_Cookie::COOKIE_RECENTLY_COMPARED);
        $recentlyComparedProducts = (empty($recentlyComparedProducts)) ? array()
            : explode(',', $recentlyComparedProducts);

        $deletedProducts = array_diff($previouseList, $ids);
        $recentlyComparedProducts = array_merge($recentlyComparedProducts, $deletedProducts);

        $addedProducts = array_diff($ids, $previouseList);
        $recentlyComparedProducts = array_diff($recentlyComparedProducts, $addedProducts);

        $recentlyComparedProducts = array_unique($recentlyComparedProducts);
        sort($recentlyComparedProducts);

        $this->_getCookie()->set(Evogue_PageCache_Model_Cookie::COOKIE_RECENTLY_COMPARED,
            implode(',', $recentlyComparedProducts));

       return $this;
    }

    public function processNewMessage(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_getCookie()->set(Evogue_PageCache_Model_Cookie::COOKIE_MESSAGE, '1');
        return $this;
    }

    public function customerLogin(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_getCookie()->updateCustomerCookies();

        try {
            $productIds = $this->_getCookie()->get(Evogue_PageCache_Model_Container_Viewedproducts::COOKIE_NAME);
            if ($productIds) {
                $productIds = explode(',', $productIds);
                Mage::getModel('reports/product_index_viewed')->registerIds($productIds);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $countLimit = Mage::getStoreConfig(Mage_Reports_Block_Product_Viewed::XML_PATH_RECENTLY_VIEWED_COUNT);
        $collection = Mage::getResourceModel('reports/product_index_viewed_collection')
            ->addIndexFilter()
            ->setAddedAtOrder()
            ->setPageSize($countLimit)
            ->setCurPage(1);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($collection);
        $productIds = $collection->load()->getLoadedIds();
        $productIds = implode(',', $productIds);
        Evogue_PageCache_Model_Cookie::registerViewedProducts($productIds, $countLimit, false);

        return $this;

    }

    public function customerLogout(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_getCookie()->updateCustomerCookies();
        return $this;
    }

    public function registerWishlistChange(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        $cookieValue = '';
        foreach (Mage::helper('wishlist')->getProductCollection() as $item) {
            $cookieValue .= ($cookieValue ? '_' : '') . $item->getId();
        }

        $this->_getCookie()->setObscure(Evogue_PageCache_Model_Cookie::COOKIE_WISHLIST, $cookieValue);

        $this->_getCookie()->setObscure(Evogue_PageCache_Model_Cookie::COOKIE_WISHLIST_ITEMS,
            'wishlist_item_count_' . Mage::helper('wishlist')->getItemCount());

        return $this;
    }

    public function registerNewOrder(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        $cacheTag = md5(Evogue_PageCache_Model_Container_Orders::CACHE_TAG_PREFIX
            . $this->_getCookie()->get(Evogue_PageCache_Model_Cookie::COOKIE_CUSTOMER));

        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($cacheTag));
        return $this;
    }

    public function processMessageClearing(Varien_Event_Observer $observer) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        $this->_getCookie()->delete(Evogue_PageCache_Model_Cookie::COOKIE_MESSAGE);
        return $this;
    }

    protected function _getCookie() {
        return Mage::getSingleton('evogue_pagecache/cookie');
    }



    protected function _checkViewedProducts() {
        $varName = Evogue_PageCache_Model_Processor::LAST_PRODUCT_COOKIE;
        $productId = (int) Mage::getSingleton('core/cookie')->get($varName);
        if ($productId) {
            $model = Mage::getModel('reports/product_index_viewed');
            if (!$model->getCount()) {
                $product = Mage::getModel('catalog/product')->load($productId);
                if ($product->getId()) {
                    $model->setProductId($productId)
                        ->save()
                        ->calculate();
                }
            }
            Mage::getSingleton('core/cookie')->delete($varName);
        }
    }
}
