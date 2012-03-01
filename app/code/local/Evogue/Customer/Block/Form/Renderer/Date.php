<?php
class Evogue_Customer_Block_Form_Renderer_Date extends Evogue_Customer_Block_Form_Renderer_Abstract {

    protected $_dateInputs  = array();

    public function getDateFormat() {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function setDateInput($code, $html) {
        $this->_dateInputs[$code] = $html;
    }

    public function getSortedDateInputs() {
        $strtr = array(
            '%b' => '%1$s',
            '%B' => '%1$s',
            '%m' => '%1$s',
            '%d' => '%2$s',
            '%e' => '%2$s',
            '%Y' => '%3$s',
            '%y' => '%3$s'
        );

        $dateFormat = preg_replace('/[^\%\w]/', '\\1', $this->getDateFormat());

        return sprintf(strtr($dateFormat, $strtr),
            $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
    }

    public function getTimestamp() {
        $timestamp         = $this->getData('timestamp');
        $attributeCodeThis = $this->getData('attribute_code');
        $attributeCodeObj  = $this->getAttributeObject()->getAttributeCode();
        if (is_null($timestamp) || $attributeCodeThis != $attributeCodeObj) {
            $value = $this->getValue();
            if ($value) {
                if (is_numeric($value)) {
                    $timestamp = $value;
                } else {
                    $timestamp = strtotime($value);
                }
            } else {
                $timestamp = false;
            }
            $this->setData('timestamp', $timestamp);
            $this->setData('attribute_code', $attributeCodeObj);
        }
        return $timestamp;
    }

    protected function _getDateValue($index) {
        if ($this->getTimestamp()) {
            return date($index, $this->getTimestamp());
        }
        return '';
    }

    public function getDay() {
        return $this->_getDateValue('d');
    }

    public function getMonth() {
        return $this->_getDateValue('m');
    }

    public function getYear() {
        return $this->_getDateValue('Y');
    }
}
