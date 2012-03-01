<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Address_Attributes
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_address_attributes');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('customer_address_save_commit_after', 'customer_address_delete_commit_after');
    }

    public function getNewChildSelectOptions() {
        $prefix = 'evogue_customersegment/segment_condition_customer_address_';
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value'=> $this->getType() . '|' . $code, 'label'=>$label);
        }
        $conditions = array_merge($conditions, Mage::getModel($prefix . 'region')->getNewChildSelectOptions());
        return array(
            'value' => $conditions,
            'label'=>Mage::helper('evogue_customersegment')->__('Address Attributes')
        );
    }

    public function loadAttributeOptions() {
        $customerAttributes = Mage::getResourceSingleton('customer/address')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();
        foreach ($customerAttributes as $attribute) {
            // skip "binary" attributes
            if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                continue;
            }
            if ($attribute->getIsUsedForCustomerSegment()) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        asort($attributes);
        $this->setAttributeOption($attributes);
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
        if (in_array($this->getAttribute(), array('country_id', 'region_id'))) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
        }

        return 'string';
    }

    public function getValueElementType() {
        if (in_array($this->getAttribute(), array('country_id', 'region_id'))) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
        }

        return 'text';
    }

    public function asHtml() {
        return Mage::helper('evogue_customersegment')->__('Customer Address %s', parent::asHtml());
    }

    public function getAttributeObject() {
        return Mage::getSingleton('eav/config')->getAttribute('customer_address', $this->getAttribute());
    }

    public function getConditionsSql($customer, $website) {
        $select = $this->getResource()->createSelect();
        $attribute = $this->getAttributeObject();

        $select->from(array('val'=>$attribute->getBackendTable()), array(new Zend_Db_Expr(1)));
        $select->limit(1);

        $condition = $this->getResource()->createConditionSql(
            'val.value', $this->getOperator(), $this->getValue()
        );
        $select->where('val.attribute_id = ?', $attribute->getId())
            ->where("val.entity_id = customer_address.entity_id")
            ->where($condition);

        return $select;
    }
}
