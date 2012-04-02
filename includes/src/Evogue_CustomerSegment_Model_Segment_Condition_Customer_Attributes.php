<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Attributes
    extends Evogue_CustomerSegment_Model_Condition_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_attributes');
        $this->setValue(null);
    }

    public function getMatchedEvents() {
        return array('customer_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value' => $this->getType() . '|' . $code, 'label' => $label);
        }

        return $conditions;
    }

    public function getAttributeObject() {
        return Mage::getSingleton('eav/config')->getAttribute('customer', $this->getAttribute());
    }

    public function loadAttributeOptions() {
        $productAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();

        foreach ($productAttributes as $attribute) {
            $label = $attribute->getFrontendLabel();
            if (!$label) {
                continue;
            }
            if (in_array($attribute->getFrontendInput(), array('file', 'image'))) {
                continue;
            }
            if ($attribute->getIsUsedForCustomerSegment()) {
                $attributes[$attribute->getAttributeCode()] = $label;
            }
        }
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getValueSelectOptions() {
        if (!$this->getData('value_select_options') && is_object($this->getAttributeObject())) {
            if ($this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $this->setData('value_select_options', $optionsArr);
            }

            if ($this->_isCurrentAttributeDefaultAddress()) {
                $optionsArr = $this->_getOptionsForAttributeDefaultAddress();
                $this->setData('value_select_options', $optionsArr);
            }
        }

        return $this->getData('value_select_options');
    }

    public function getInputType() {
        if ($this->_isCurrentAttributeDefaultAddress()) {
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
            default:
                return 'string';
        }
    }

    public function getValueElementType() {
        if ($this->_isCurrentAttributeDefaultAddress()) {
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
            default:
                return 'text';
        }
    }

    public function getValueElement() {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                    break;
            }
        }
        return $element;
    }

    public function getExplicitApply() {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }
        return false;
    }

    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getOperatorElementHtml() {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return '';
        }
        return parent::getOperatorElementHtml();
    }

    protected function _isCurrentAttributeDefaultAddress() {
        $code = $this->getAttributeObject()->getAttributeCode();
        return $code == 'default_billing' || $code == 'default_shipping';
    }

    protected function _getOptionsForAttributeDefaultAddress() {
        return array(
            array(
                'value' => 'is_exists',
                'label' => Mage::helper('evogue_customersegment')->__('exists')
            ),
            array(
                'value' => 'is_not_exists',
                'label' => Mage::helper('evogue_customersegment')->__('does not exist')
            ),
        );
    }

    public function asHtml() {
        return Mage::helper('evogue_customersegment')->__('Customer %s', parent::asHtml());
    }

    public function getDateValue() {
        if ($this->getOperator() == '==') {
            $dateObj = Mage::app()->getLocale()
                ->date($this->getValue(), Varien_Date::DATE_INTERNAL_FORMAT, null, false)
                ->setHour(0)->setMinute(0)->setSecond(0);
            $value = array(
                'start' => $dateObj->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
                'end' => $dateObj->addDay(1)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
            return $value;
        }
        return $this->getValue();
    }

    public function getDateOperator() {
        if ($this->getOperator() == '==') {
            return 'between';
        }
        return $this->getOperator();
    }

    public function getConditionsSql($customer, $website) {
        $attribute = $this->getAttributeObject();
        $table = $attribute->getBackendTable();
        $addressTable = $this->getResource()->getTable('customer/address_entity');

        $select = $this->getResource()->createSelect();
        $select->from(array('main'=>$table), array(new Zend_Db_Expr(1)))
            ->limit(1);
        $select->where($this->_createCustomerFilter($customer, 'main.entity_id'));

        if (!in_array($attribute->getAttributeCode(), array('default_billing', 'default_shipping')) ) {
            $value    = $this->getValue();
            $operator = $this->getOperator();
            if ($attribute->isStatic()) {
                $field = "main.{$attribute->getAttributeCode()}";
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
                $field = 'main.value';
            }
            if ($attribute->getFrontendInput() == 'date') {
                $value    = $this->getDateValue();
                $operator = $this->getDateOperator();
            }
            $condition = $this->getResource()->createConditionSql($field, $operator, $value);
            $select->where($condition);
        } else {
            $joinFunction = 'joinLeft';
            if ($this->getValue() == 'is_exists') {
                $joinFunction = 'joinInner';
            } else {
                $select->where('address.entity_id IS NULL');
            }
            $select->$joinFunction(array('address'=>$addressTable), 'address.entity_id = main.value', array());
            $select->where('main.attribute_id = ?', $attribute->getId());
        }
        return $select;
    }
}
