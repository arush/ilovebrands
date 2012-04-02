<?php
class Evogue_TargetRule_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('evogue_targetrule/rule');
    }

    public function addApplyToFilter($applyTo) {
        $this->addFieldToFilter('apply_to', $applyTo);
        return $this;
    }

    public function addIsActiveFilter($isActive = 1) {
        $this->addFieldToFilter('is_active', $isActive);
        return $this;
    }

    public function setPriorityOrder($direction = self::SORT_ORDER_DESC) {
        $this->setOrder('sort_order', $direction);
        return $this;
    }

    protected function _afterLoad() {
        if ($this->getFlag('add_customersegment_relations')) {
            $this->getResource()->addCustomerSegmentRelationsToCollection($this);
        }

        foreach ($this->_items as $rule) {
            if (!$this->getFlag('do_not_run_after_load')) {
                $rule->afterLoad();
            }
        }

        return parent::_afterLoad();
    }

    public function addProductFilter($productId) {
        $this->getSelect()->join(
            array('product_idx' => $this->getTable('evogue_targetrule/product')),
            'product_idx.rule_id = main_table.rule_id',
            array()
        );
        $this->getSelect()->where('product_idx.product_id=?', $productId);

        return $this;
    }
}
