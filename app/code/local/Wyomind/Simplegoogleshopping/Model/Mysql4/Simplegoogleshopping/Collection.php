<?php

class Wyomind_Simplegoogleshopping_Model_Mysql4_Simplegoogleshopping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('simplegoogleshopping/simplegoogleshopping');
    }
}