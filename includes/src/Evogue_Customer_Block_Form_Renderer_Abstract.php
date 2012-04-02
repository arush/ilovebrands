<?php
abstract class Evogue_Customer_Block_Form_Renderer_Abstract extends Mage_Core_Block_Template {

    protected $_attribute;

    protected $_entity;

    protected $_fieldIdFormat   = '%1$s';

    protected $_fieldNameFormat = '%1$s';

    public function setAttributeObject(Mage_Customer_Model_Attribute $attribute) {
        $this->_attribute = $attribute;
        return $this;
    }

    public function getAttributeObject() {
        return $this->_attribute;
    }

    public function setEntity(Mage_Core_Model_Abstract $entity) {
        $this->_entity = $entity;
        return $this;
    }

    public function getEntity() {
        return $this->_entity;
    }

    protected function _getFormFilter() {
        $filterCode = $this->getAttributeObject()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Varien_Data_Form_Filter_' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $filter = new $filterClass($format);
            } else {
                $filter = new $filterClass();
            }
            return $filter;
        }
        return false;
    }

    protected function _applyOutputFilter($value) {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    protected function _getInputValidateClass() {
        $class          = false;
        $validateRules  = $this->getAttributeObject()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                case 'date':
                    // @todo DATE FORMAT
                    break;
            }
        }
        return $class;
    }

    protected function _getValidateClasses($withRequired = true) {
        $classes = array();
        if ($withRequired && $this->isRequired()) {
            $classes[] = 'required-entry';
        }
        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
            $classes[] = $inputRuleClass;
        }
        return $classes;
    }

    public function getValue() {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        return $value;
    }

    public function getHtmlId($index = null) {
        $format = $this->_fieldIdFormat;
        if (!is_null($index)) {
            $format .= '_%2$s';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    public function getFieldName($index = null) {
        $format = $this->_fieldNameFormat;
        if (!is_null($index)) {
            $format .= '[%2$s]';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    public function getHtmlClass() {
        $classes = $this->_getValidateClasses(true);
        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    public function isRequired() {
        return $this->getAttributeObject()->getIsRequired();
    }

    public function getLabel() {
        return $this->getAttributeObject()->getStoreLabel();
    }

    public function setFieldIdFormat($format) {
        $this->_fieldIdFormat = $format;
        return $this;
    }

    public function setFieldNameFormat($format) {
        $this->_fieldNameFormat = $format;
        return $this;
    }
}
