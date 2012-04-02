<?php
class Evogue_TargetRule_Block_Adminhtml_Rule_Conditions implements Varien_Data_Form_Element_Renderer_Interface {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        if ($element->getRule() && $element->getRule()->getConditions()) {
           return $element->getRule()->getConditions()->asHtmlRecursive();
        }
        return '';
    }
}
