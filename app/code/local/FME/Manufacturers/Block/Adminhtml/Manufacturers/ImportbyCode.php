<?php

class FME_Manufacturers_Block_Adminhtml_Manufacturers_ImportbyCode extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('manufacturers/importbycode.phtml');
    }
}