<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Product_Attributes
    extends Mage_CatalogRule_Model_Rule_Condition_Product {
    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_product_attributes');
        $this->setValue(null);
    }

    public function getDefaultOperatorInputByType() {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
            $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
            $this->_defaultOperatorInputByType['category'] = array('{}', '!{}');
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getInputType() {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->getAttributeObject()->getAttributeCode()=='category_ids') {
            return 'category';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    public function getNewChildSelectOptions() {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value'=> $this->getType() . '|' . $code, 'label'=>$label);
        }

        return array(
            'value' => $conditions,
            'label' => Mage::helper('evogue_customersegment')->__('Product Attributes')
        );
    }

    public function asHtml() {
        return Mage::helper('evogue_customersegment')->__('Product %s', parent::asHtml());
    }

    public function getAttributeObject() {
        return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->getAttribute());
    }

    public function getResource()
    {
        return Mage::getResourceSingleton('evogue_customersegment/segment');
    }

    public function getSubfilterType() {
        return 'product';
    }

    public function getSubfilterSql($fieldName, $requireValid, $website) {
        $attribute = $this->getAttributeObject();
        $table = $attribute->getBackendTable();

        $resource = $this->getResource();
        $select = $resource->createSelect();
        $select->from(array('main'=>$table), array('entity_id'));

        if ($attribute->getAttributeCode() == 'category_ids') {
            $condition = $resource->createConditionSql(
                'cat.category_id', $this->getOperator(), explode(',', $this->getValue())
            );
            $categorySelect = $resource->createSelect();
            $categorySelect->from(array('cat'=>$resource->getTable('catalog/category_product')), 'product_id')
                ->where($condition);
            $condition = 'main.entity_id IN ('.$categorySelect.')';
        } elseif ($attribute->isStatic()) {
            $condition = $this->getResource()->createConditionSql(
                "main.{$attribute->getAttributeCode()}", $this->getOperator(), $this->getValue()
            );
        } else {
            $select->where('main.attribute_id = ?', $attribute->getId());
            $select->join(
                    array('store'=> $this->getResource()->getTable('core/store')),
                    'main.store_id=store.store_id',
                    array())
                ->where('store.website_id IN(?)', array(0, $website));
            $condition = $this->getResource()->createConditionSql(
                'main.value', $this->getOperator(), $this->getValue()
            );
        }
        $select->where($condition);
        $inOperator = ($requireValid ? 'IN' : 'NOT IN');
        return sprintf("%s %s (%s)", $fieldName, $inOperator, $select);
    }
}
