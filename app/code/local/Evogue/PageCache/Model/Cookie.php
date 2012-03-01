<?php
class Evogue_PageCache_Model_Cookie extends Mage_Core_Model_Cookie {

    const COOKIE_CUSTOMER           = 'CUSTOMER';
    const COOKIE_CUSTOMER_GROUP     = 'CUSTOMER_INFO';

    const COOKIE_MESSAGE            = 'NEWMESSAGE';
    const COOKIE_CART               = 'CART';
    const COOKIE_COMPARE_LIST       = 'COMPARE';
    const COOKIE_RECENTLY_COMPARED  = 'RECENTLYCOMPARED';
    const COOKIE_WISHLIST           = 'WISHLIST';
    const COOKIE_WISHLIST_ITEMS     = 'WISHLIST_CNT';

    const COOKIE_CATEGORY_PROCESSOR = 'CATEGORY_INFO';

    protected $_salt = null;

    protected function _getSalt() {
        if ($this->_salt === null) {
            $saltCacheId = 'full_page_cache_key';
            $this->_salt = Mage::app()->getCache()->load($saltCacheId);
            if (!$this->_salt) {
                $this->_salt = md5(microtime() . rand());
                Mage::app()->getCache()->save($this->_salt, $saltCacheId,
                    array(Evogue_PageCache_Model_Processor::CACHE_TAG));
            }
        }
        return $this->_salt;
    }

    public function setObscure($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        $value = md5($this->_getSalt() . $value);
        return $this->set($name, $value, $period, $path, $domain, $secure, $httponly);
    }

    public function updateCustomerCookies() {

        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->setObscure(self::COOKIE_CUSTOMER, 'customer_' . $session->getCustomerId());
            $this->setObscure(self::COOKIE_CUSTOMER_GROUP, 'customer_group_' . $session->getCustomerGroupId());
        } else {
            $this->delete(self::COOKIE_CUSTOMER);
            $this->delete(self::COOKIE_CUSTOMER_GROUP);
        }
    }

    public static function registerViewedProducts($productIds, $countLimit, $append = true) {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        if ($append) {
            if (!empty($_COOKIE[Evogue_PageCache_Model_Container_Viewedproducts::COOKIE_NAME])) {
                $cookieIds = $_COOKIE[Evogue_PageCache_Model_Container_Viewedproducts::COOKIE_NAME];
                $cookieIds = explode(',', $cookieIds);
            } else {
                $cookieIds = array();
            }
            array_splice($cookieIds, 0, 0, $productIds);  // append to the beginning
        } else {
            $cookieIds = $productIds;
        }
        $cookieIds = array_unique($cookieIds);
        $cookieIds = array_slice($cookieIds, 0, $countLimit);
        $cookieIds = implode(',', $cookieIds);
        setcookie(Evogue_PageCache_Model_Container_Viewedproducts::COOKIE_NAME, $cookieIds, 0, '/');
    }

    public static function setCategoryCookieValue($value) {
        setcookie(self::COOKIE_CATEGORY_PROCESSOR, $value, 0, '/');
    }

    public static function getCategoryCookieValue() {
        return (isset($_COOKIE[self::COOKIE_CATEGORY_PROCESSOR])) ? $_COOKIE[self::COOKIE_CATEGORY_PROCESSOR] : false;
    }
}
