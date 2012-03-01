<?php
class Evogue_Customer_Block_Form_Renderer_Multiline extends Evogue_Customer_Block_Form_Renderer_Abstract {

    public function getValues() {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        if (!is_array($value)) {
            $value = explode("\n", $value);
        }
        return $value;
    }

    public function getLineCount() {
        return $this->getAttributeObject()->getMultilineCount();
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

    public function getLineHtmlClass() {
        $classes = $this->_getValidateClasses(false);
        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    public function getEscapedValue($index) {
        $values = $this->getValues();
        if (isset($values[$index])) {
            $value = $values[$index];
        } else {
            $value = '';
        }

        return $this->htmlEscape($this->_applyOutputFilter($value));
    }
}
