<?php
class Evogue_Customer_Block_Form_Renderer_Multiselect extends Evogue_Customer_Block_Form_Renderer_Select {

    public function getOptions() {
        return $this->getAttributeObject()->getSource()->getAllOptions();
    }

    public function getValues() {
        $value = $this->getValue();
        return explode(',', $value);
    }

    public function isValueSelected($value) {
        return in_array($value, $this->getValues());
    }
}
