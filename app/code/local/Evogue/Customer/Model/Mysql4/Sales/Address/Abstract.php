<?php
abstract class Evogue_Customer_Model_Mysql4_Sales_Address_Abstract extends Evogue_Customer_Model_Mysql4_Sales_Abstract {

    protected $_columnPrefix    = null;


    public function attachDataToCollection(Varien_Data_Collection_Db $collection) {
        $items      = array();
        $itemIds    = array();
        foreach($collection->getItems() as $item) {
            $itemIds[] = $item->getId();
            $items[$item->getId()] = $item;
        }

        if ($itemIds) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where("{$this->getIdFieldName()} IN (?)", $itemIds);
            $rowSet = $this->_getReadAdapter()->fetchAll($select);
            foreach ($rowSet as $row) {
                $items[$row[$this->getIdFieldName()]]->addData($row);
            }
        }
        
        return $this;
    }
}
