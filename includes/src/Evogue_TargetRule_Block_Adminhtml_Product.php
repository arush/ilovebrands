<?php
class Evogue_TargetRule_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget {

    protected $_readOnly = false;

    protected function _getRuleHelper() {
        return Mage::helper('evogue_targetrule');
    }

    protected function _getProductListType() {
        $listType = '';
        switch ($this->getFormPrefix()) {
            case 'related':
                $listType = Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS;
                break;
            case 'upsell':
                $listType = Evogue_TargetRule_Model_Rule::UP_SELLS;
                break;
        }
        return $listType;
    }

    public function getProduct() {
        return Mage::registry('current_product');
    }

    public function getPositionBehaviorOptions() {
        return Mage::getModel('evogue_targetrule/source_position')->toOptionArray();
    }

    public function getPositionLimit() {
        $position = $this->_getValue('position_limit');
        if (is_null($position)) {
            $position = $this->_getRuleHelper()->getMaximumNumberOfProduct($this->_getProductListType());
        }
        return $position;
    }

    public function getPositionBehavior() {
        $show = $this->_getValue('position_behavior');
        if (is_null($show)) {
            $show = $this->_getRuleHelper()->getShowProducts($this->_getProductListType());
        }
        return $show;
    }

    protected function _getValue($field) {
        return $this->getProduct()->getDataUsingMethod($this->getFieldName($field));
    }

    public function getFieldName($field) {
        return $this->getFormPrefix() . '_targetrule_' . $field;
    }

    public function isDefault($value) {
        return ($this->_getValue($value) === null) ? true : false;
    }

    public function setIsReadonly($flag) {
        return $this->setData('is_readonly', (bool)$flag);
    }

    public function getIsReadonly() {
        $flag = $this->_getData('is_readonly');
        if (is_null($flag)) {
            $flag = false;
        }
        return $flag;
    }
}
