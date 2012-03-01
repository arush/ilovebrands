<?php
class Evogue_Customer_Block_Form_Renderer_Select extends Evogue_Customer_Block_Form_Renderer_Abstract {

    public function getOptions() {
        return $this->getAttributeObject()->getSource()->getAllOptions();
    }
}
