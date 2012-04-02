<?php
class Evogue_TargetRule_Model_Actions_Condition_Combine extends Mage_Rule_Model_Condition_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/actions_condition_combine');
    }

    public function getNewChildSelectOptions() {
        $conditions = array(
            array('value'=>$this->getType(),
                'label'=>Mage::helper('evogue_targetrule')->__('Conditions Combination')),
            Mage::getModel('evogue_targetrule/actions_condition_product_attributes')->getNewChildSelectOptions(),
            Mage::getModel('evogue_targetrule/actions_condition_product_special')->getNewChildSelectOptions(),
        );
        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    public function getConditionForCollection($collection, $object, &$bind) {
        $conditions = array();
        $aggregator = $this->getAggregator() == 'all' ? ' AND ' : ' OR ';
        $operator   = $this->getValue() ? '1' : '0';

        foreach ($this->getConditions() as $condition) {
            $subCondition = $condition->getConditionForCollection($collection, $object, $bind);
            if ($subCondition) {
                $conditions[] = sprintf('%s=%d', $subCondition, $operator);
            }
        }

        if ($conditions) {
            return new Zend_Db_Expr(sprintf('(%s)', join($aggregator, $conditions)));
        }

        return false;
    }
}

