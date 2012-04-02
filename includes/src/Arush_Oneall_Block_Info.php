<?php

class Arush_Oneall_Block_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {

        $html = $this->_getHeaderHtml($element);

        $html.= $this->_getFieldHtml($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldHtml($fieldset) {
        $content = '<p>Arush Oneall for Magento ' . Mage::getConfig()->getModuleConfig("Arush_Oneall")->version . '</p>';
        $content.= '<p>Please see <a href="http://www.oneall.com/" target="_blank">Oneall.com</a> on how to install and configure this extension.</p>';
		
        return $content;
    }

}
