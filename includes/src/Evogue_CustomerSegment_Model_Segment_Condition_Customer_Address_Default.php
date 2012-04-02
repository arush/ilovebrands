<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Address_Default
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    protected $_inputType = 'select';

    public function __construct()
    {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_address_default');
        $this->setValue('default_billing');
    }

    public function getMatchedEvents() {
        return array('customer_address_save_commit_after', 'customer_save_commit_after', 'customer_address_delete_commit_after');
    }

        public function getNewChildSelectOptions() {
        return array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Default Address')
        );
    }

    public function loadValueOptions() {
        $this->setValueOption(array(
            'default_billing'  => Mage::helper('evogue_customersegment')->__('Billing'),
            'default_shipping' => Mage::helper('evogue_customersegment')->__('Shipping'),
        ));
        return $this;
    }

    public function getValueElementType() {
        return 'select';
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('Customer Address %s Default %s Address',
                $this->getOperatorElementHtml(), $this->getValueElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer, $website){
        $select = $this->getResource()->createSelect();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $this->getValue());
        $select->from(array('default'=>$attribute->getBackendTable()), array(new Zend_Db_Expr(1)));
        $select->limit(1);

        $select->where('default.attribute_id = ?', $attribute->getId())
            ->where('default.value=customer_address.entity_id')
            ->where($this->_createCustomerFilter($customer, 'default.entity_id'));

        return $select;
    }
}
