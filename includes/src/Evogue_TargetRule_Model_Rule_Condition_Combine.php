<?php
class Evogue_TargetRule_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine {
    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/rule_condition_combine');
    }

    public function getNewChildSelectOptions() {
        $conditions = array(
            array(
                'value' => $this->getType(),
                'label' => Mage::helper('evogue_targetrule')->__('Conditions Combination')
            ),
            Mage::getModel('evogue_targetrule/rule_condition_product_attributes')->getNewChildSelectOptions(),
        );

        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection) {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
