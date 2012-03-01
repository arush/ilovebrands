<?php
class Evogue_CatalogEvent_Block_Adminhtml_Form_Renderer_Fieldset_Element extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {
    protected function _construct() {
        $this->setTemplate('evogue/catalogevent/form/renderer/fieldset/element.phtml');
    }

    public function getDataObject() {
        return $this->getElement()->getForm()->getDataObject();
    }

    public function canDisplayUseDefault() {
        if ($element = $this->getElement()) {
            if ($element->getScope() != 'global'
                && $element->getScope() != null
                && $this->getDataObject()
                && $this->getDataObject()->getId()
                && $this->getDataObject()->getStoreId()) {
                return true;
            }
        }
        return false;
    }

    public function usedDefault() {
        $defaultValue = $this->getDataObject()->getData($this->getElement()->getId() . '_default');
        return $defaultValue === null;
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
            $html.= '[GLOBAL]';
        }
        elseif ($element->getScope() == 'website') {
            $html.= '[WEBSITE]';
        }
        elseif ($element->getScope() == 'store') {
            $html.= '[STORE VIEW]';
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
