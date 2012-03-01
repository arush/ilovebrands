<?php
class Evogue_TargetRule_Model_Source_Position {

    public function toOptionArray() {
        return array(
            Evogue_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED =>
                Mage::helper('evogue_targetrule')->__('Both Selected and Rule-Based'),
            Evogue_TargetRule_Model_Rule::SELECTED_ONLY =>
                Mage::helper('evogue_targetrule')->__('Selected Only'),
            Evogue_TargetRule_Model_Rule::RULE_BASED_ONLY =>
                Mage::helper('evogue_targetrule')->__('Rule-Based Only'),
        );
    }

}
