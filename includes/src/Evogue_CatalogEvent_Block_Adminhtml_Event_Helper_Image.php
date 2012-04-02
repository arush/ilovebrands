<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Helper_Image extends Varien_Data_Form_Element_Image {
    protected function _getUrl() {
        $url = false;
        if ($this->getValue()) {
            $url = $this->getForm()->getDataObject()->getImageUrl();
        }
        return $url;
    }

    public function getDefaultName() {
        $name = $this->getData('name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

}
