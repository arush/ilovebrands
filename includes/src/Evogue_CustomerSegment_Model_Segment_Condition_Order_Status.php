<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Order_Status
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    const VALUE_ANY = 'any';
    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_order_status');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Order Status')
        );
    }

    public function getValueElementType() {
        return 'select';
    }

    public function loadValueOptions() {
        $this->setValueOption(array_merge(
            array(self::VALUE_ANY => Mage::helper('evogue_customersegment')->__('Any')),
            Mage::getSingleton('sales/order_config')->getStatuses())
        );
        return $this;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Order Status %s %s:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getAttributeObject() {
        return Mage::getSingleton('eav/config')->getAttribute('order', 'status');
    }

    public function getSubfilterType() {
        return 'order';
    }

    public function getSubfilterSql($fieldName, $requireValid, $website) {
        if ($this->getValue() == self::VALUE_ANY) {
            return '';
        }
        return $this->getResource()->createConditionSql($fieldName, $this->getOperator(), $this->getValue());
    }
}
