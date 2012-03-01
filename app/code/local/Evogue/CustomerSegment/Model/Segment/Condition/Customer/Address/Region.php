<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Address_Region
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    protected $_inputType = 'select';


    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_address_region');
        $this->setValue(1);
    }

    public function getMatchedEvents() {
        return Mage::getModel('evogue_customersegment/segment_condition_customer_address_attributes')
            ->getMatchedEvents();
    }

    public function getNewChildSelectOptions() {
        return array(array(
            'value' => $this->getType(),
            'label' => Mage::helper('evogue_customersegment')->__('Has State/Province')
        ));
    }

    public function asHtml() {
        $element = $this->getValueElementHtml();
        return $this->getTypeElementHtml()
            .Mage::helper('evogue_customersegment')->__('If Customer Address %s State/Province specified', $element)
            .$this->getRemoveLinkHtml();
    }

    public function getValueElementType() {
        return 'select';
    }

    public function loadValueOptions() {
        $this->setValueOption(array(
            '1' => Mage::helper('evogue_customersegment')->__('has'),
            '0' => Mage::helper('evogue_customersegment')->__('does not have'),
        ));
        return $this;
    }

    public function getConditionsSql($customer, $website) {
        $inversion = ((int)$this->getValue() ? '' : '!');
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'region');
        $select = $this->getResource()->createSelect();
        $select->from(array('caev'=>$attribute->getBackendTable()), "{$inversion}(IFNULL(caev.value, '') <> '')");
        $select->where('caev.attribute_id = ?', $attribute->getId())
            ->where("caev.entity_id = customer_address.entity_id");
        $select->limit(1);

        return $select;
    }
}
