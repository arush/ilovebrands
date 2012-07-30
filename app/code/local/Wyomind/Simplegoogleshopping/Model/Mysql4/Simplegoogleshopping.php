<?php

class Wyomind_Simplegoogleshopping_Model_Mysql4_Simplegoogleshopping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
     
        // Note that the simplegoogleshopping_id refers to the key field in your database table.
        $this->_init('simplegoogleshopping/simplegoogleshopping', 'simplegoogleshopping_id');
    }
}