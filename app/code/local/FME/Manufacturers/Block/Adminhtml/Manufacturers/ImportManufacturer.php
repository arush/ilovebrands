<?php

class FME_Manufacturers_Block_Adminhtml_Manufacturers_importManufacturer extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('manufacturers/importManufacturer.phtml');
    }
}