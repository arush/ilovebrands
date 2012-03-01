<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Order_Address_Attributes
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    protected $_attributes;

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_order_address_attributes');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('sales_order_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array(
                'value' => $this->getType() . '|' . $code,
                'label' => $label
            );
        }

        return array(
            'value' => $conditions,
            'label' => Mage::helper('evogue_customersegment')->__('Order Address Attributes')
        );
    }

    public function loadAttributeOptions() {
        if (is_null($this->_attributes)) {
            $this->_attributes  = array();

            $config     = Mage::getSingleton('eav/config');
            $attributes = array();

            foreach ($config->getEntityAttributeCodes('customer_address') as $attributeCode) {
                $attribute = $config->getAttribute('customer_address', $attributeCode);
                if (!$attribute || !$attribute->getIsUsedForCustomerSegment()) {
                    continue;
                }

                if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                    continue;
                }
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
            }

            $this->setAttributeOption($attributes);
        }

        return $this;
    }

    public function getValueSelectOptions() {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType() {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region_id':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType() {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region_id':
                return 'select';
        }
        return 'text';
    }

    public function asHtml() {
        return Mage::helper('evogue_customersegment')->__('Order Address %s', parent::asHtml());
    }

    public function getAttributeObject() {
        $this->loadAttributeOptions();
        return $this->_attributes[$this->getAttribute()];
    }

    public function getConditionsSql($customer, $website) {
        if ($this->getAttributeObject()->getIsUserDefined()) {
            $tableAlias = 'extra_order_address';
        } else {
            $tableAlias = 'order_address';
        }

        return $this->getResource()->createConditionSql(
            sprintf('%s.%s', $tableAlias, $this->getAttribute()),
            $this->getOperator(),
            $this->getValue()
        );
    }
}
