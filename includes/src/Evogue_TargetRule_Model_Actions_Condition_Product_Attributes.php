<?php
class Evogue_TargetRule_Model_Actions_Condition_Product_Attributes
    extends Evogue_TargetRule_Model_Rule_Condition_Product_Attributes {

    const VALUE_TYPE_CONSTANT       = 'constant';
    const VALUE_TYPE_SAME_AS        = 'same_as';
    const VALUE_TYPE_CHILD_OF       = 'child_of';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/actions_condition_product_attributes');
        $this->setValue(null);
        $this->setValueType(self::VALUE_TYPE_SAME_AS);
    }

    protected function _addSpecialAttributes(array &$attributes) {
        parent::_addSpecialAttributes($attributes);
        $attributes['type_id'] = Mage::helper('catalogrule')->__('Type');
    }

    public function getValueOption($option = null) {
        if (!$this->getData('value_option') && $this->getAttribute() == 'type_id') {
            $options = Mage::getSingleton('catalog/product_type')->getAllOption();
            $this->setData('value_option', $options);
        }
        return parent::getValueOption($option);
    }

    public function getValueSelectOptions() {
        if (!$this->getData('value_select_options') && $this->getAttribute() == 'type_id') {
            $options = Mage::getSingleton('catalog/product_type')->getAllOptions();
            $this->setData('value_select_options', $options);
        }
        return parent::getValueSelectOptions();
    }

    public function getInputType() {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        } else if ($attributeCode == 'category_ids') {
            return 'grid';
        }
        return parent::getInputType();
    }

    public function getValueElementType() {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        }
        return parent::getValueElementType();
    }

    public function asHtml() {
        return Mage::helper('evogue_targetrule')->__('Product %s%s%s%s%s%s%s', $this->getTypeElementHtml(), $this->getAttributeElementHtml(), $this->getOperatorElementHtml(), $this->getValueTypeElementHtml(), $this->getValueElementHtml(), $this->getRemoveLinkHtml(), $this->getChooserContainerHtml());
    }

    public function getValueTypeOptions() {
        $options = array(
            array(
                'value' => self::VALUE_TYPE_CONSTANT,
                'label' => Mage::helper('evogue_targetrule')->__('Constant Value')
            )
        );

        if ($this->getAttribute() == 'category_ids') {
            $options[] = array(
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => Mage::helper('evogue_targetrule')->__('the Same as Matched Product Categories')
            );
            $options[] = array(
                'value' => self::VALUE_TYPE_CHILD_OF,
                'label' => Mage::helper('evogue_targetrule')->__('the Child of the Matched Product Categories')
            );
        } else {
            $options[] = array(
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => Mage::helper('evogue_targetrule')->__('Matched Product %s', $this->getAttributeName())
            );
        }

        return $options;
    }

    public function getValueTypeName() {
        $options = $this->getValueTypeOptions();
        foreach ($options as $option) {
            if ($option['value'] == $this->getValueType()) {
                return $option['label'];
            }
        }
        return '...';
    }

    public function getValueTypeElement() {
        $elementId  = $this->getPrefix().'__'.$this->getId().'__value_type';
        $element    = $this->getForm()->addField($elementId, 'select', array(
            'name'          => 'rule['.$this->getPrefix().']['.$this->getId().'][value_type]',
            'values'        => $this->getValueTypeOptions(),
            'value'         => $this->getValueType(),
            'value_name'    => $this->getValueTypeName(),
            'class'         => 'value-type-chooser',
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
        return $element;
    }

    public function getValueTypeElementHtml() {
        $element = $this->getValueTypeElement();
        return $element->getHtml();
    }

    public function loadArray($array) {
        parent::loadArray($array);

        if (isset($array['value_type'])) {
            $this->setValueType($array['value_type']);
        }
        return $this;
    }

    public function asArray(array $arrAttributes = array()) {
        $array = parent::asArray($arrAttributes);
        $array['value_type'] = $this->getValueType();
        return $array;
    }

    public function asString($format = '') {
        if (!$format) {
            $format = ' %s %s %s %s';
        }
        return sprintf(Mage::helper('evogue_targetrule')->__('Target Product ') . $format,
           $this->getAttributeName(),
           $this->getOperatorName(),
           $this->getValueTypeName(),
           $this->getValueName()
        );
    }

    public function getConditionForCollection($collection, $object, &$bind) {

        $attributeCode  = $this->getAttribute();
        $valueType      = $this->getValueType();
        $operator       = $this->getOperator();
        $resource       = $object->getResource();

        if ($attributeCode == 'category_ids') {
            $select = $object->select()
                ->from($resource->getTable('catalog/category_product'), 'COUNT(*) > 0')
                ->where('product_id=e.entity_id');
            if ($valueType == self::VALUE_TYPE_SAME_AS) {
                $where = $resource->getOperatorBindCondition('category_id', 'category_ids', $operator, $bind,
                    array('bindArrayOfIds'));
                $select->where($where);
            } else if ($valueType == self::VALUE_TYPE_CHILD_OF) {
                $subSelect = $resource->select()
                    ->from(array('tc' => $resource->getTable('catalog/category')), 'entity_id')
                    ->join(
                        array('tp' => $resource->getTable('catalog/category')),
                        "tc.path ".($operator == '!()' ? 'NOT ' : '')."LIKE CONCAT(tp.path, '/%')",
                        array())
                    ->where($resource->getOperatorBindCondition('tp.entity_id', 'category_ids', '()', $bind,
                        array('bindArrayOfIds')));
                $select->where('category_id IN(?)', $subSelect);
            } else { //self::VALUE_TYPE_CONSTANT
                $value = $resource->bindArrayOfIds($this->getValue());
                $where = $resource->getOperatorCondition('category_id', $operator, $value);
                $select->where($where);
            }

            return new Zend_Db_Expr(sprintf('(%s)', $select->assemble()));
        }

        if ($valueType == self::VALUE_TYPE_CONSTANT) {
            $useBind = false;
            $value = $this->getValue();
            // split value by commas into array for operators with multiple operands
            if (($operator == '()' || $operator == '!()') && is_string($value) && trim($value) != '') {
                $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            }
        } else { //self::VALUE_TYPE_SAME_AS
            $useBind = true;
        }

        $attribute = $this->getAttributeObject();
        if (!$attribute) {
            return false;
        }

        if ($attribute->isStatic()) {
            $field = "e.{$attributeCode}";
            if ($useBind) {
                $where = $resource->getOperatorBindCondition($field, $attributeCode, $operator, $bind);
            } else {
                $where = $resource->getOperatorCondition($field, $operator, $value);
            }
            $where = sprintf('(%s)', $where);
        } else if ($attribute->isScopeGlobal()) {
            $table  = $attribute->getBackendTable();
            $select = $object->select()
                ->from(array('table' => $table), 'COUNT(*) > 0')
                ->where('table.entity_id = e.entity_id')
                ->where('table.attribute_id=?', $attribute->getId())
                ->where('table.store_id=?', 0);
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition('table.value', $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition('table.value', $operator, $value));
            }

            $where  = sprintf('IFNULL((%s), 0)', $select->assemble());
        } else { //scope store and website
            $valueExpr = 'IF(attr_s.value_id > 0, attr_s.value, attr_d.value)';

            $table  = $attribute->getBackendTable();
            $select = $object->select()
                ->from(array('attr_d' => $table), 'COUNT(*) > 0')
                ->joinLeft(
                    array('attr_s' => $table),
                    'attr_s.entity_id = attr_d.entity_id AND attr_s.attribute_id = attr_d.attribute_id'
                        . ' AND attr_s.store_id=' . $object->getStoreId(),
                    array())
                ->where('attr_d.entity_id = e.entity_id')
                ->where('attr_d.attribute_id=?', $attribute->getId())
                ->where('attr_d.store_id=?', 0);
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition($valueExpr, $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition($valueExpr, $operator, $value));
            }

            $where  = sprintf('(%s)', $select->assemble());
        }

        return new Zend_Db_Expr($where);
    }
}
