<?php
class Evogue_Customer_Block_Adminhtml_Form_Renderer_Fieldset_Element
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {

    public function getDataObject() {
        return $this->getElement()->getForm()->getDataObject();
    }

    public function canDisplayUseDefault() {
        $element = $this->getElement();
        if ($element) {
            if ($element->getScope() != 'global' && $element->getScope() != null && $this->getDataObject()
                && $this->getDataObject()->getId() && $this->getDataObject()->getWebsite()->getId()) {
                return true;
            }
        }
        return false;
    }

    public function usedDefault() {
        $key = $this->getElement()->getId();
        if (strpos($key, 'default_value_') === 0) {
            $key = 'default_value';
        }
        $storeValue = $this->getDataObject()->getData('scope_' . $key);
        return ($storeValue === null);
    }

    public function checkFieldDisable() {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    public function getScopeLabel() {
        $html = '';
        $element = $this->getElement();
        if (Mage::app()->isSingleStoreMode()) {
            return $html;
        }

        if ($element->getScope() == 'global' || $element->getScope() === null) {
            $html = Mage::helper('evogue_customer')->__('[GLOBAL]');
        } else if ($element->getScope() == 'website') {
            $html = Mage::helper('evogue_customer')->__('[WEBSITE]');
        } else if ($element->getScope() == 'store') {
            $html = Mage::helper('evogue_customer')->__('[STORE VIEW]');
        }

        return $html;
    }

    public function getElementLabelHtml() {
        return $this->getElement()->getLabelHtml();
    }

    public function getElementHtml() {
        return $this->getElement()->getElementHtml();
    }
}
