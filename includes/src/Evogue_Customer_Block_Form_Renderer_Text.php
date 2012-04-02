<?php
class Evogue_Customer_Block_Form_Renderer_Text extends Evogue_Customer_Block_Form_Renderer_Abstract {
    
    public function getEscapedValue() {
        return $this->htmlEscape($this->_applyOutputFilter($this->getValue()));
    }

    protected function _getValidateClasses($withRequired = true) {
        $classes    = parent::_getValidateClasses($withRequired);
        $rules      = $this->getAttributeObject()->getValidateRules();
        if (!empty($rules['min_text_length'])) {
            $classes[] = 'validate-length';
            $classes[] = 'minimum-length-' . $rules['min_text_length'];
        }
        if (!empty($rules['max_text_length'])) {
            if (!in_array('validate-length', $classes)) {
                $classes[] = 'validate-length';
            }
            $classes[] = 'maximum-length-' . $rules['max_text_length'];
        }

        return $classes;
    }
}
