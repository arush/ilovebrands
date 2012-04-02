<?php
class Evogue_TargetRule_Model_Rule extends Mage_Rule_Model_Rule {
    const BOTH_SELECTED_AND_RULE_BASED  = 0;
    const SELECTED_ONLY                 = 1;
    const RULE_BASED_ONLY               = 2;

    const RELATED_PRODUCTS              = 1;
    const UP_SELLS                      = 2;
    const CROSS_SELLS                   = 3;

    const XML_PATH_DEFAULT_VALUES       = 'catalog/evogue_targetrule/';

    protected $_products;

    protected $_productIds;

    protected $_checkDateForStore = array();

    protected function _construct() {
        $this->_init('evogue_targetrule/rule');
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        if ($this->dataHasChangedFor('actions_serialized')) {
            $this->setData('action_select', null);
            $this->setData('action_select_bind', null);
        }

        return $this;
    }

    public function getConditionsInstance() {
        return Mage::getModel('evogue_targetrule/rule_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('evogue_targetrule/actions_condition_combine');
    }

    public function loadPost(array $rule) {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }
        return $this;
    }

    public function getAppliesToOptions($withEmpty = false) {
        $result = array();
        if ($withEmpty) {
            $result[''] = Mage::helper('adminhtml')->__('-- Please Select --');
        }
        $result[Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS]
            = Mage::helper('evogue_targetrule')->__('Related Products');
        $result[Evogue_TargetRule_Model_Rule::UP_SELLS]
            = Mage::helper('evogue_targetrule')->__('Up-sells');
        $result[Evogue_TargetRule_Model_Rule::CROSS_SELLS]
            = Mage::helper('evogue_targetrule')->__('Cross-sells');
        return $result;
    }

    public function getCustomerSegmentRelations() {
        if (!$this->getUseCustomerSegment() || !$this->getId()) {
            return array();
        }
        $relations = $this->_getData('customer_segment_relations');
        if (!is_array($relations)) {
            $relations = $this->_getResource()->getCustomerSegmentRelations($this->getId());
            $this->setData('customer_segment_relations', $relations);
        }

        return $relations;
    }

    public function setCustomerSegmentRelations($relations) {
        if (is_array($relations)) {
            $this->setData('customer_segment_relations', $relations);
        } else if (is_string($relations)) {
            if (empty($relations)) {
                $relations = array();
            } else {
                $relations = explode(',', $relations);
            }
            $this->setData('customer_segment_relations', $relations);
        }

        return $this;
    }

    public function getMatchingProducts() {
        if (is_null($this->_products)) {
            $productCollection = Mage::getResourceModel('catalog/product_collection');

            $this->setCollectedAttributes(array());
            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_productIds = array();
            $this->_products   = array();
            Mage::getSingleton('core/resource_iterator')->walk(
                $productCollection->getSelect(),
                array(
                    array($this, 'callbackValidateProduct')
                ),
                array(
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => Mage::getModel('catalog/product'),
                )
            );
        }

        return $this->_products;
    }

    public function getMatchingProductIds() {
        if (is_null($this->_productIds)) {
            $this->getMatchingProducts();
        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args) {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
            $this->_products[]   = $product;
        }
    }

    public function checkDateForStore($storeId) {
        if (!isset($this->_checkDateForStore[$storeId])) {
            $this->_checkDateForStore[$storeId] = Mage::app()->getLocale()
                ->isStoreDateInInterval(null, $this->getFromDate(), $this->getToDate());
        }
        return $this->_checkDateForStore[$storeId];
    }

    public function getPositionsLimit() {
        $limit = $this->getData('positions_limit');
        if (!$limit) {
            $limit = 20;
        }
        return $limit;
    }

    public function getActionSelectBind() {
        $bind = $this->getData('action_select_bind');
        if (!is_null($bind) && !is_array($bind)) {
            $bind = unserialize($bind);
        }

        return $bind;
    }

    public function setActionSelectBind($bind) {
        if (is_array($bind)) {
            $bind = serialize($bind);
        }
        return $this->setData('action_select_bind', $bind);
    }

    public function getActions() {
        return parent::getActions();
    }

    public function validate(Varien_Object $object) {
        if($object->getData('from_date') && $object->getData('to_date')){
            $dateStartUnixTime = strtotime($object->getData('from_date'));
            $dateEndUnixTime   = strtotime($object->getData('to_date'));

            if ($dateEndUnixTime < $dateStartUnixTime) {
                return array(Mage::helper('evogue_targetrule')->__("End Date should be greater than Start Date"));
            }
        }
        return true;
    }
}
