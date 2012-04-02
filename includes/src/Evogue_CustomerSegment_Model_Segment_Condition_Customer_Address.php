<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Address
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_address');
    }

    public function getNewChildSelectOptions() {
        $prefix = 'evogue_customersegment/segment_condition_customer_address_';
        $result = array_merge_recursive(parent::getNewChildSelectOptions(), array(
            array(
                'value' => $this->getType(),
                'label' => Mage::helper('evogue_customersegment')->__('Conditions Combination')
            ),
            Mage::getModel($prefix.'default')->getNewChildSelectOptions(),
            Mage::getModel($prefix.'attributes')->getNewChildSelectOptions(),
        ));
        return $result;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('If Customer Addresses match %s of these Conditions:',
                $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getValue() {
        return 1;
    }

    protected function _prepareConditionsSql($customer, $website) {
        $resource = $this->getResource();
        $select = $resource->createSelect();
        $addressEntityType = Mage::getSingleton('eav/config')->getEntityType('customer_address');
        $addressTable = $resource->getTable($addressEntityType->getEntityTable());

        $select->from(array('customer_address' => $addressTable), array(new Zend_Db_Expr(1)));
        $select->where('customer_address.entity_type_id = ?', $addressEntityType->getId());
        $select->where($this->_createCustomerFilter($customer, 'customer_address.parent_id'));
        $select->limit(1);
        return $select;
    }

}
