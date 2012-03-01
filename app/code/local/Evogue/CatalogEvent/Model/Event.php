<?php
class Evogue_CatalogEvent_Model_Event extends Mage_Core_Model_Abstract {
    const DISPLAY_CATEGORY_PAGE = 1;
    const DISPLAY_PRODUCT_PAGE  = 2;

    const STATUS_UPCOMING       = 'upcoming';
    const STATUS_OPEN           = 'open';
    const STATUS_CLOSED         = 'closed';

    const CACHE_TAG             = 'catalog_event';

    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';

    const IMAGE_PATH = 'evogue/catalogevent';

    protected $_store = null;

    protected $_cacheTag        = self::CACHE_TAG;

    protected $_isDeleteable = true;

    protected $_isReadonly = false;

    protected function _construct() {
        $this->_init('evogue_catalogevent/event');
    }

    public function getCacheIdTags() {
        $tags = parent::getCacheIdTags();
        if ($this->getCategoryId()) {
            $tags[] = Mage_Catalog_Model_Category::CACHE_TAG.'_'.$this->getCategoryId();
        }
        return $tags;
    }

    protected function _afterLoad() {
        $this->_initDisplayStateArray();
        parent::_afterLoad();
        $this->getStatus();
        return $this;
    }

    protected function _initDisplayStateArray() {
        $state = array();
        if ($this->canDisplayCategoryPage()) {
            $state[] = self::DISPLAY_CATEGORY_PAGE;
        }
        if ($this->canDisplayProductPage()) {
            $state[] = self::DISPLAY_PRODUCT_PAGE;
        }
        $this->setDisplayStateArray($state);
        return $this;
    }

    public function setStoreId($storeId = null) {
        $this->_store = Mage::app()->getStore($storeId);
        return $this;
    }

    public function getStore()
    {
        if ($this->_store === null) {
            $this->setStoreId();
        }

        return $this->_store;
    }

    public function setImage($value) {
        if ($value instanceof Varien_File_Uploader) {
            $value->save(Mage::getBaseDir('media') . DS
                         . strtr(self::IMAGE_PATH, '/', DS));

            $value = $value->getUploadedFileName();
        }

        $this->setData('image', $value);
        return $this;
    }

    public function getImageUrl() {
        if ($this->getImage()) {
            return Mage::getBaseUrl('media') . '/'
                   . self::IMAGE_PATH . '/' . $this->getImage();
        }

        return false;
    }

    public function getStoreId() {
        return $this->getStore()->getId();
    }

    public function setDisplayState($state) {
        if (is_array($state)) {
            $value = 0;
            foreach ($state as $_state) {
                $value ^= $_state;
            }
            $this->setData('display_state', $value);
        } else {
            $this->setData('display_state', $state);
        }
        return $this;
    }

    public function canDisplay($state) {
        return ((int) $this->getDisplayState() & $state) == $state;
    }

    public function canDisplayProductPage() {
        return $this->canDisplay(self::DISPLAY_PRODUCT_PAGE);
    }

    public function canDisplayCategoryPage() {
        return $this->canDisplay(self::DISPLAY_CATEGORY_PAGE);
    }

    public function applyStatusByDates() {
        if ($this->getDateStart() && $this->getDateEnd()) {
            $timeStart = $this->_getResource()->mktime($this->getDateStart()); // Date already in gmt, no conversion
            $timeEnd = $this->_getResource()->mktime($this->getDateEnd()); // Date already in gmt, no conversion
            $timeNow = gmdate('U');
            if ($timeStart <= $timeNow && $timeEnd >= $timeNow) {
                $this->setStatus(self::STATUS_OPEN);
            } elseif ($timeNow > $timeEnd) {
                $this->setStatus(self::STATUS_CLOSED);
            } else {
                $this->setStatus(self::STATUS_UPCOMING);
            }
        }
        return $this;
    }

    public function getCategoryIdsWithEvent($storeId = null) {
        return $this->_getResource()->getCategoryIdsWithEvent($storeId);
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $dateChanged = false;
        $fieldTitles = array('date_start' => Mage::helper('evogue_catalogevent')->__('Start Date') , 'date_end' => Mage::helper('evogue_catalogevent')->__('End Date'));
        foreach (array('date_start' , 'date_end') as $dateType) {
            $date = $this->getData($dateType);
            if (empty($date)) { // Date fields is required.
                Mage::throwException(Mage::helper('evogue_catalogevent')->__('%s is required.', $fieldTitles[$dateType]));
            }
            if ($date != $this->getOrigData($dateType)) {
                $dateChanged = true;
            }
        }
        if ($dateChanged) {
            $this->applyStatusByDates();
        }

        return $this;
    }

    public function validate() {
        $dateStartUnixTime = strtotime($this->getData('date_start'));
        $dateEndUnixTime   = strtotime($this->getData('date_end'));
        $dateIsOk = $dateEndUnixTime > $dateStartUnixTime;
        if ($dateIsOk) {
            return true;
        }
        else {
            return array(Mage::helper('evogue_catalogevent')->__('End date should be greater than start date.'));
        }
    }

    protected function _convertDateTime($dateTime, $format) {
        $date = new Zend_Date(Mage::app()->getLocale()->getLocale());
        $date->setTimezone(Mage::app()->getStore()->getConfig(self::XML_PATH_DEFAULT_TIMEZONE));
        $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        $date->set($dateTime, $format);
        $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    public function isDeleteable() {
        return $this->_isDeleteable;
    }

    public function setIsDeleteable($value) {
        $this->_isDeleteable = (boolean) $value;
        return $this;
    }

    public function isReadonly() {
        return $this->_isReadonly;
    }

    public function setIsReadonly($value) {
        $this->_isReadonly = (boolean) $value;
        return $this;
    }

    public function updateStatus() {
        $originalStatus = $this->getStatus();
        if ($originalStatus == self::STATUS_OPEN || $originalStatus == self::STATUS_UPCOMING) {
            $this->applyStatusByDates();
            if ($this->getStatus() != $originalStatus) {
                $this->save();
            }
        }
    }

    public function getStatus() {
        if (!$this->hasData('status')) {
            $this->applyStatusByDates();
        }
        return $this->_getData('status');
    }

    public function setStoreDateStart($value, $store = null) {
        $date = Mage::app()->getLocale()->utcDate($store, $value, true, Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->setData('date_start', $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        return $this;
    }

    public function setStoreDateEnd($value, $store = null) {
        $date = Mage::app()->getLocale()->utcDate($store, $value, true, Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->setData('date_end', $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        return $this;
    }

    public function getStoreDateStart($store = null) {
        if ($this->getData('date_start')) {
            $value = $this->getResource()->mktime($this->getData('date_start'));
            if (!$value) {
                return null;
            }
            $date = Mage::app()->getLocale()->storeDate($store, $value, true);
            return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return $this->getData('date_start');
    }

    public function getStoreDateEnd($store = null) {
        if ($this->getData('date_end')) {
            $value = $this->getResource()->mktime($this->getData('date_end'));
            if (!$value) {
                return null;
            }
            $date = Mage::app()->getLocale()->storeDate($store, $value, true);
            return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return $this->getData('date_end');
    }
}
