<?php
class Evogue_TargetRule_Model_Actions_Condition_Product_Special_Price
    extends Evogue_TargetRule_Model_Actions_Condition_Product_Special {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/actions_condition_product_special_price');
        $this->setValue(100);
    }

    protected function _getOperatorOptionArray() {
        return array(
            '==' => Mage::helper('evogue_targetrule')->__('equal to'),
            '>'  => Mage::helper('evogue_targetrule')->__('more'),
            '>=' => Mage::helper('evogue_targetrule')->__('equals or greater than'),
            '<'  => Mage::helper('evogue_targetrule')->__('less'),
            '<=' => Mage::helper('evogue_targetrule')->__('equals or less than')
        );
    }

    public function loadOperatorOptions() {
        parent::loadOperatorOptions();
        $this->setOperatorOption($this->_getOperatorOptionArray());
        return $this;
    }

    public function asHtml() {
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_targetrule')->__('Product Price is %s %s%% of Matched Product(s) Price',
                $this->getOperatorElementHtml(),
                $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionForCollection($collection, $object, &$bind) {

        $resource       = $object->getResource();
        $operator       = $this->getOperator();

        $where = $resource->getOperatorBindCondition('price_index.min_price', 'final_price', $operator, $bind,
            array(array('bindPercentOf', $this->getValue())));
        return new Zend_Db_Expr(sprintf('(%s)', $where));
    }
}
