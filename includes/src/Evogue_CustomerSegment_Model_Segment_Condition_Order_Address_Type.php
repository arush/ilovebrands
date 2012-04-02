<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Order_Address_Type
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_order_address_type');
        $this->setValue('shipping');
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Address Type')
        );
    }

    public function loadValueOptions() {
        $this->setValueOption(array(
            'shipping' => Mage::helper('evogue_customersegment')->__('Shipping'),
            'billing'  => Mage::helper('evogue_customersegment')->__('Billing'),
        ));
        return $this;
    }

    public function getValueElementType() {
        return 'select';
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Order Address %s a %s Address',
                $this->getOperatorElementHtml(), $this->getValueElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer, $website) {
        return $this->getResource()->createConditionSql(
            'order_address.address_type',
            $this->getOperator(),
            $this->getValue()
        );
    }
}
