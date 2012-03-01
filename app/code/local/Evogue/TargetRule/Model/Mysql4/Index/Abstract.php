<?php
abstract class Evogue_TargetRule_Model_Mysql4_Index_Abstract extends Mage_Core_Model_Mysql4_Abstract {

    protected $_listType;

    public function getListType() {
        if (is_null($this->_listType)) {
            Mage::throwException(
                Mage::helper('evogue_targetrule')->__('Product list type identifier does not defined')
            );
        }
        return $this->_listType;
    }

    public function setListType($listType) {
        $this->_listType = $listType;
        return $this;
    }

    public function getProductResource() {
        return Mage::getResourceSingleton('catalog/product');
    }

    public function loadProductIds($object) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'value')
            ->where('entity_id=?', $object->getProduct()->getEntityId())
            ->where('store_id=?', $object->getStoreId())
            ->where('customer_group_id=?', $object->getCustomerGroupId());

        $value  = $this->_getReadAdapter()->fetchOne($select);
        if (!empty($value)) {
            $productIds = explode(',', $value);
        } else {
            $productIds = array();
        }

        return $productIds;
    }

    public function saveResult($object, $value) {
        $adapter = $this->_getWriteAdapter();
        $data    = array(
            'entity_id'         => $object->getProduct()->getEntityId(),
            'store_id'          => $object->getStoreId(),
            'customer_group_id' => $object->getCustomerGroupId(),
            'value'             => $value
        );

        $adapter->insertOnDuplicate($this->getMainTable(), $data, array('value'));

        return $this;
    }

    public function removeIndex($entityIds) {
        $this->_getWriteAdapter()->delete($this->getMainTable(), array(
            'entity_id IN(?)'   => $entityIds
        ));

        return $this;
    }

    public function cleanIndex($store = null) {
        if (is_null($store)) {
            $this->_getWriteAdapter()->truncate($this->getMainTable());
            return $this;
        }
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }
        $where = array('store_id IN(?)' => $store);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }
}