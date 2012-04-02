<?php
class Evogue_Customer_Block_Form_Renderer_File extends Evogue_Customer_Block_Form_Renderer_Abstract {

    public function getEscapedValue() {
        if ($this->getValue()) {
            return $this->escapeHtml(Mage::helper('core')->urlEncode($this->getValue()));
        }
        return '';
    }
}
