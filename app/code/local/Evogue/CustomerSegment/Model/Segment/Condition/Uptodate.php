<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Uptodate extends Evogue_CustomerSegment_Model_Condition_Abstract {
    protected $_inputType = 'numeric';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_uptodate');
        $this->setValue(null);
    }

    public function getDefaultOperatorInputByType() {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['numeric'] = array('>=', '<=', '>', '<');
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getDefaultOperatorOptions() {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = array(
                '<='  => Mage::helper('rule')->__('equals or greater than'),
                '>='  => Mage::helper('rule')->__('equals or less than'),
                '<'   => Mage::helper('rule')->__('greater than'),
                '>'   => Mage::helper('rule')->__('less than')
            );
        }
        return $this->_defaultOperatorOptions;
    }

    public function getNewChildSelectOptions() {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Up To Date'),
        );
    }

    public function getValueElementType() {
        return 'text';
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Period %s %s Days Up To Date',
                $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }


    public function getSubfilterType() {
        return 'date';
    }

    public function getSubfilterSql($fieldName, $requireValid, $website) {
        $value = $this->getValue();
        if (!$value || !is_numeric($value)) {
            return false;
        }

        $limit = date('Y-m-d', strtotime("now -{$value} days"));
        $operator = $this->getOperator();
        return sprintf("%s %s '%s'", $fieldName, $operator, $limit);
    }
}
