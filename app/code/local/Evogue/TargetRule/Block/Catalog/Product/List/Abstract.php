<?php
abstract class Evogue_TargetRule_Block_Catalog_Product_List_Abstract extends Mage_Catalog_Block_Product_Abstract {

    protected $_linkCollection;

    protected $_items;

    protected $_index;

    protected $_excludeProductIds;

    abstract public function getType();

    public function getProduct() {
        return Mage::registry('product');
    }

    public function getTargetRuleHelper() {
        return Mage::helper('evogue_targetrule');
    }

    protected function _getTypePrefix() {
        switch ($this->getType()) {
            case Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $prefix = 'related';
                break;

            case Evogue_TargetRule_Model_Rule::UP_SELLS:
                $prefix = 'upsell';
                break;

            default:
                Mage::throwException(
                    Mage::helper('evogue_targetrule')->__('Undefined Catalog Product List Type')
                );
        }
        return $prefix;
    }

    protected function _getTargetRuleIndex() {
        if (is_null($this->_index)) {
            $this->_index = Mage::getModel('evogue_targetrule/index');
        }
        return $this->_index;
    }

    protected function _getPositionLimitField() {
        return sprintf('%s_targetrule_position_limit', $this->_getTypePrefix());
    }

    protected function _getPositionBehaviorField() {
        return sprintf('%s_targetrule_position_behavior', $this->_getTypePrefix());
    }

    public function getPositionLimit() {
        $limit = $this->getProduct()->getData($this->_getPositionLimitField());
        if (is_null($limit)) { // use configuration settings
            $limit = $this->getTargetRuleHelper()->getMaximumNumberOfProduct($this->getType());
            $this->getProduct()->setData($this->_getPositionLimitField(), $limit);
        }
        return $this->getTargetRuleHelper()->getMaxProductsListResult($limit);
    }

    public function getPositionBehavior() {
        $behavior = $this->getProduct()->getData($this->_getPositionBehaviorField());
        if (is_null($behavior)) { // use configuration settings
            $behavior = $this->getTargetRuleHelper()->getShowProducts($this->getType());
            $this->getProduct()->setData($this->_getPositionBehaviorField(), $behavior);
        }
        return $behavior;
    }

    public function getExcludeProductIds() {
        if (is_null($this->_excludeProductIds)) {
            $this->_excludeProductIds = array($this->getProduct()->getEntityId());
        }
        return $this->_excludeProductIds;
    }

    public function getLinkCollection() {
        if (is_null($this->_linkCollection)) {
            switch ($this->getType()) {
                case Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS:
                    $this->_linkCollection = $this->getProduct()
                        ->getRelatedProductCollection();
                    break;

                case Evogue_TargetRule_Model_Rule::UP_SELLS:
                    $this->_linkCollection = $this->getProduct()
                        ->getUpSellProductCollection();
                    break;

                default:
                    Mage::throwException(
                        Mage::helper('evogue_targetrule')->__('Undefined Catalog Product List Type')
                    );
            }

            $this->_addProductAttributesAndPrices($this->_linkCollection);
            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInCatalogFilterToCollection($this->_linkCollection);

            $this->_linkCollection->addAttributeToSort('position', 'ASC')
                ->setFlag('do_not_use_category_id', true)
                ->setPageSize($this->getPositionLimit());

            $excludeProductIds = $this->getExcludeProductIds();
            if ($excludeProductIds) {
                $this->_linkCollection->addAttributeToFilter('entity_id', array('nin' => $excludeProductIds));
            }
        }

        return $this->_linkCollection;
    }

    public function getLinkCollectionCount() {
        return count($this->getLinkCollection()->getItems());
    }

    public function getItemCollection() {
        if (is_null($this->_items)) {
            $ruleBased  = array(
                Evogue_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
                Evogue_TargetRule_Model_Rule::RULE_BASED_ONLY,
            );
            $selected   = array(
                Evogue_TargetRule_Model_Rule::BOTH_SELECTED_AND_RULE_BASED,
                Evogue_TargetRule_Model_Rule::SELECTED_ONLY,
            );
            $behavior   = $this->getPositionBehavior();
            $limit      = $this->getPositionLimit();

            $this->_items = array();

            if (in_array($behavior, $selected)) {
                foreach ($this->getLinkCollection() as $item) {
                    $this->_items[$item->getEntityId()] = $item;
                }
            }

            if (in_array($behavior, $ruleBased) && $limit > count($this->_items)) {
                $excludeProductIds = array_merge(array_keys($this->_items), $this->getExcludeProductIds());

                $count = $limit - count($this->_items);
                $productIds = $this->_getTargetRuleIndex()
                    ->setType($this->getType())
                    ->setLimit($count)
                    ->setProduct($this->getProduct())
                    ->setExcludeProductIds($excludeProductIds)
                    ->getProductIds();

                if ($productIds) {
                    $collection = Mage::getResourceModel('catalog/product_collection');
                    $collection->addFieldToFilter('entity_id', array('in' => $productIds));
                    $this->_addProductAttributesAndPrices($collection);

                    Mage::getSingleton('catalog/product_visibility')
                        ->addVisibleInCatalogFilterToCollection($collection);
                    $collection->setPageSize($count)
                        ->setFlag('do_not_use_category_id', true);

                    $orderedAr = array_flip($productIds);

                    foreach ($collection as $item) {
                        $this->_items[(int)$orderedAr[$item->getEntityId()]] = $item;
                    }
                    ksort($this->_items);
                }
            }
        }

        return $this->_items;
    }

    public function hasItems() {
        return $this->getItemsCount() > 0;
    }

    public function getItemsCount() {
        return count($this->getItemCollection());
    }
}
