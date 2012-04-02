<?php
abstract class Evogue_Customer_Model_Mysql4_Sales_Abstract extends Mage_Core_Model_Mysql4_Abstract {

    protected $_columnPrefix        = 'customer';

    protected $_isPkAutoIncrement   = false;

    protected function _getColumnName(Mage_Customer_Model_Attribute $attribute) {
        if ($this->_columnPrefix) {
            return sprintf('%s_%s', $this->_columnPrefix, $attribute->getAttributeCode());
        }
        return $attribute->getAttributeCode();
    }


    public function saveNewAttribute(Mage_Customer_Model_Attribute $attribute) {
        $backendType = $attribute->getBackendType();
        if ($backendType == Mage_Customer_Model_Attribute::TYPE_STATIC) {
            return $this;
        }

        switch ($backendType) {
            case 'datetime':
                $defination = "DATE NULL DEFAULT NULL";
                break;
            case 'decimal':
                $defination = "DECIMAL(12,4) DEFAULT NULL";
                break;
            case 'int':
                $defination = "INT(11) DEFAULT NULL";
                break;
            case 'text':
                $defination = "TEXT DEFAULT NULL";
                break;
            case 'varchar':
                $defination = "VARCHAR(255) DEFAULT NULL";
                break;
            default:
                return $this;
        }

        $this->_getWriteAdapter()->addColumn($this->getMainTable(), $this->_getColumnName($attribute), $defination);

        return $this;
    }

    public function deleteAttribute(Mage_Customer_Model_Attribute $attribute) {
        $this->_getWriteAdapter()->dropColumn($this->getMainTable(), $this->_getColumnName($attribute));
        return $this;
    }
}
