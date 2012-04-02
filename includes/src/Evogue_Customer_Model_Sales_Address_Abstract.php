<?php
abstract class Evogue_Customer_Model_Sales_Address_Abstract extends Evogue_Customer_Model_Sales_Abstract {

    public function attachDataToCollection(Varien_Data_Collection_Db $collection) {
        $this->_getResource()->attachDataToCollection($collection);
        return $this;
    }
}
