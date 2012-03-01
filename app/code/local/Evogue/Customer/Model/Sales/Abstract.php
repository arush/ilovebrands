<?php
abstract class Evogue_Customer_Model_Sales_Abstract extends Mage_Core_Model_Abstract {

    public function saveNewAttribute(Mage_Customer_Model_Attribute $attribute) {
        $this->_getResource()->saveNewAttribute($attribute);
        return $this;
    }

    public function deleteAttribute(Mage_Customer_Model_Attribute $attribute) {
        $this->_getResource()->deleteAttribute($attribute);
        return $this;
    }


    public function attachAttributeData(Mage_Core_Model_Abstract $sales) {
        $sales->addData($this->getData());
        return $this;
    }


    public function saveAttributeData(Mage_Core_Model_Abstract $sales) {
        $this->addData($sales->getData())
            ->setId($sales->getId())
            ->save();

        return $this;
    }
}
