<?php
abstract class Evogue_CatalogEvent_Block_Event_Abstract extends Mage_Core_Block_Template {

    protected $_statuses;

    protected function _construct() {
        parent::_construct();
        $this->_statuses = array(
            Evogue_CatalogEvent_Model_Event::STATUS_UPCOMING => $this->helper('evogue_catalogevent')->__('Coming Soon'),
            Evogue_CatalogEvent_Model_Event::STATUS_OPEN     => $this->helper('evogue_catalogevent')->__('Sale Ends In'),
            Evogue_CatalogEvent_Model_Event::STATUS_CLOSED   => $this->helper('evogue_catalogevent')->__('Closed'),
        );
    }

    public function getStatusText($event) {
        if (isset($this->_statuses[$event->getStatus()])) {
            return $this->_statuses[$event->getStatus()];
        }

        return '';
    }

    public function getEventTime($type, $event, $format = null) {
        if ($format === null) {
            $format = $this->_getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }

        return $this->_getEventDate($type, $event, $format);
    }

    public function getEventDate($type, $event, $format = null) {
        if ($format === null) {
            $format = $this->_getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }

        return $this->_getEventDate($type, $event, $format);

    }

    public function getEventDateTime($type, $event) {
        return $this->getEventDate($type, $event) . ' ' . $this->getEventDate($type, $event);
    }

    protected function _getEventDate($type, $event, $format) {
        $date = new Zend_Date($this->_getLocale()->getLocale());
        // changing timezone to UTC
        $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

        $dateString = $event->getData('date_' . $type);
        $date->set($dateString, Varien_Date::DATETIME_INTERNAL_FORMAT);

        if (($timezone = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE))) {
            // changing timezone to default store timezone
            $date->setTimezone($timezone);
        }
        return $date->toString($format);
    }

    public function getSecondsToClose($event) {
        $endTime = strtotime($event->getDateEnd());
        $currentTime = gmdate('U');

        return $endTime - $currentTime;
    }

    protected function _getLocale() {
        return Mage::app()->getLocale();
    }

    abstract public function canDisplay();

}
