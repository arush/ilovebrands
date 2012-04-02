<?php
class Evogue_TargetRule_Model_Index extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('evogue_targetrule/index');
    }

    protected function _getResource() {
        return parent::_getResource();
    }

    public function setType($type) {
        return $this->setData('type', $type);
    }

    public function getType() {
        $type = $this->getData('type');
        if (is_null($type)) {
            Mage::throwException(
                Mage::helper('evogue_targetrule')->__('Undefined Catalog Product List Type')
            );
        }
        return $type;
    }

    public function setStoreId($storeId) {
        return $this->setData('store_id', $storeId);
    }

    public function getStoreId() {
        $storeId = $this->getData('store_id');
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return $storeId;
    }

    public function setCustomerGroupId($customerGroupId) {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    public function getCustomerGroupId() {
        $customerGroupId = $this->getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $customerGroupId;
    }

    public function setLimit($limit) {
        return $this->setData('limit', $limit);
    }

    public function getLimit() {
        $limit = $this->getData('limit');
        if (is_null($limit)) {
            $limit = Mage::helper('evogue_targetrule')->getMaximumNumberOfProduct($this->getType());
        }
        return $limit;
    }

    public function setProduct(Varien_Object $product) {
        return $this->setData('product', $product);
    }

    public function getProduct() {
        $product = $this->getData('product');
        if (!$product instanceof Varien_Object) {
            Mage::throwException(Mage::helper('evogue_targetrule')->__('Please define product data object'));
        }
        return $product;
    }

    public function setExcludeProductIds($productIds) {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        return $this->setData('exclude_product_ids', $productIds);
    }

    public function getExcludeProductIds() {
        $productIds = $this->getData('exclude_product_ids');
        if (!is_array($productIds)) {
            $productIds = array();
        }
        return $productIds;
    }

    public function getProductIds() {
        return $this->_getResource()->getProductIds($this);
    }

    public function getRuleCollection() {

        $collection = Mage::getResourceModel('evogue_targetrule/rule_collection');
        $collection->addApplyToFilter($this->getType())
            ->addProductFilter($this->getProduct()->getId())
            ->addIsActiveFilter()
            ->setPriorityOrder()
            ->setFlag('do_not_run_after_load', true);

        return $collection;
    }

    public function select() {
        return $this->_getResource()->select();
    }

    public function cron() {
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {
            $store = $website->getDefaultStore();
            $date  = Mage::app()->getLocale()->storeDate($store);
            if ($date->equals(0, Zend_Date::HOUR)) {
                $this->_getResource()->cleanIndex(null, $website->getStoreIds());
            }
        }
    }
}
