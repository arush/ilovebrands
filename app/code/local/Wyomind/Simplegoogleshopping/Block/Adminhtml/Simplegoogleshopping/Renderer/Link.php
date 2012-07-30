<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

   
    public function render(Varien_Object $row)
    {
        $fileName = preg_replace('/^\//', '', $row->getSimplegoogleshoppingPath() . $row->getSimplegoogleshoppingFilename());
        $url = $this->htmlEscape(Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);

        if (file_exists(BP . DS . $fileName)) {
            return sprintf('<a href="%1$s?r='.time().'" target="_blank">%1$s</a>', $url);
        }
        return $url;
    }

}
