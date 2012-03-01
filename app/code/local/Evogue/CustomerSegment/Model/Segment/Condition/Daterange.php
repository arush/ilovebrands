<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Daterange
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    protected $_inputType = 'select';

    private $_valueElement = null;

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_daterange');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Date Range'),
        );
    }

    public function getValueElementType() {
        return 'text';
    }

    public function getExplicitApply() {
        return true;
    }

    public function getValueSelectOptions() {
        return array();
    }

    public function getValueAfterElementHtml() {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="'
            . Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger"'
            . 'title="' . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
    }

    public function getValueElementChooserUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/customersegment/chooserDaterange', array(
            'value_element_id' => $this->_valueElement->getId(),
        ));
    }

    public function asHtml() {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Date Range %s within %s',
                $this->getOperatorElementHtml(), $this->_valueElement->getHtml())
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser no-split" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }

    public function getSubfilterType() {
        return 'date';
    }

    public function getSubfilterSql($fieldName, $requireValid, $website) {
        $value = explode('...', $this->getValue());
        if (!isset($value[0]) || !isset($value[1])) {
            return false;
        }

        $regexp = '#^\d{4}-\d{2}-\d{2}$#';
        if (!preg_match($regexp, $value[0]) || !preg_match($regexp, $value[1])) {
            return false;
        }

        $start = $value[0];
        $end = $value[1];

        if (!$start || !$end) {
            return false;
        }

        $inOperator = (($requireValid && $this->getOperator() == '==') ? 'BETWEEN' : 'NOT BETWEEN');
        return sprintf("%s %s '%s' AND '%s'", $fieldName, $inOperator, $start, $end);
    }
}
