<?php
class Evogue_TargetRule_Model_Mysql4_Index extends Mage_Core_Model_Mysql4_Abstract {

    protected $_bindIncrement = 0;

    protected function _construct() {
        $this->_init('evogue_targetrule/index', 'entity_id');
    }

    public function getOverfillLimit() {
        return 20;
    }

    public function getTypeIndex($type) {
        switch ($type) {
            case Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS:
                $model = 'related';
                break;

            case Evogue_TargetRule_Model_Rule::UP_SELLS:
                $model = 'upsell';
                break;

            case Evogue_TargetRule_Model_Rule::CROSS_SELLS:
                $model = 'crosssell';
                break;

            default:
                Mage::throwException(
                    Mage::helper('evogue_targetrule')->__('Undefined Catalog Product List Type')
                );
        }

        return Mage::getResourceSingleton('evogue_targetrule/index_' . $model);
    }

    public function getTypeIds() {
        return array(
            Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS,
            Evogue_TargetRule_Model_Rule::UP_SELLS,
            Evogue_TargetRule_Model_Rule::CROSS_SELLS
        );
    }

    public function getProductIds($object) {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'flag')
            ->where('type_id=?', $object->getType())
            ->where('entity_id=?', $object->getProduct()->getEntityId())
            ->where('store_id=?', $object->getStoreId())
            ->where('customer_group_id=?', $object->getCustomerGroupId());
        $flag    = $adapter->fetchOne($select);

        if (empty($flag)) {
            $productIds = $this->_matchProductIds($object);
            if ($productIds) {
                $value = join(',', $productIds);
                $this->getTypeIndex($object->getType())
                    ->saveResult($object, $value);
            }
            $this->saveFlag($object);
        } else {
            $productIds = $this->getTypeIndex($object->getType())
                ->loadProductIds($object);
        }

        $productIds = array_diff($productIds, $object->getExcludeProductIds());
        shuffle($productIds);

        return array_slice($productIds, 0, $object->getLimit());
    }

    protected function _matchProductIds($object) {
        $limit      = $object->getLimit() + $this->getOverfillLimit();
        $productIds = array();
        $ruleCollection = $object->getRuleCollection();
        foreach ($ruleCollection as $rule) {
            if (count($productIds) >= $limit) {
                continue;
            }
            if (!$rule->checkDateForStore($object->getStoreId())) {
                continue;
            }

            $resultIds = $this->_getProductIdsByRule($rule, $object, $limit, $productIds);
            $productIds = array_merge($productIds, $resultIds);
        }

        return $productIds;
    }

    protected function _getProductIdsByRule($rule, $object, $limit, $excludeProductIds = array()) {
        $rule->afterLoad();

        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($object->getStoreId())
            ->addPriceData($object->getCustomerGroupId());
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        $actionSelect = $rule->getActionSelect();
        $actionBind   = $rule->getActionSelectBind();
        if (is_null($actionSelect)) {
            $actionBind   = array();
            $actionSelect = $rule->getActions()->getConditionForCollection($collection, $object, $actionBind);
            $rule->setActionSelect((string)$actionSelect)
                ->setActionSelectBind($actionBind)
                ->save();
        }

        if ($actionSelect) {
            $collection->getSelect()->where($actionSelect);
        }
        if ($excludeProductIds) {
            $collection->addFieldToFilter('entity_id', array('nin' => $excludeProductIds));
        }

        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('entity_id', 'e');
        $select->limit($limit);

        $bind   = $this->_prepareRuleActionSelectBind($object, $actionBind);
        $result = $select->getAdapter()->fetchCol($select, $bind);

        return $result;
    }

    protected function _prepareRuleActionSelectBind($object, $actionBind) {
        $bind = array();
        if (!is_array($actionBind)) {
            $actionBind = array();
        }

        foreach ($actionBind as $bindData) {
            if (! is_array($bindData) || ! array_key_exists('bind', $bindData) || ! array_key_exists('field', $bindData)) {
                continue;
            }
            $k = $bindData['bind'];
            $v = $object->getProduct()->getDataUsingMethod($bindData['field']);

            if (!empty($bindData['callback'])) {
                $callbacks = $bindData['callback'];
                if (!is_array($callbacks)) {
                    $callbacks = array($callbacks);
                }
                foreach ($callbacks as $callback) {
                    if (is_array($callback)) {
                        $v = $this->$callback[0]($v, $callback[1]);
                    } else {
                        $v = $this->$callback($v);
                    }
                }
            }

            if (is_array($v)) {
                $v = join(',', $v);
            }

            $bind[$k] = $v;
        }

        return $bind;
    }

    public function saveFlag($object) {
        $data = array(
            'type_id'           => $object->getType(),
            'entity_id'         => $object->getProduct()->getEntityId(),
            'store_id'          => $object->getStoreId(),
            'customer_group_id' => $object->getCustomerGroupId(),
            'flag'              => 1
        );

        $this->_getWriteAdapter()->insert($this->getMainTable(), $data);

        return $this;
    }

    public function select() {
        return $this->_getReadAdapter()->select();
    }

    public function getOperatorCondition($field, $operator, $value) {
        switch ($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':
                $selectOperator = sprintf('%s?', $operator);
                break;
            case '{}':
                $selectOperator = ' LIKE ?';
                $value          = '%' . $value . '%';
                break;

            case '!{}':
                $selectOperator = ' NOT LIKE ?';
                $value          = '%' . $value . '%';
                break;

            case '()':
                $selectOperator = ' IN(?)';
                break;

            case '!()':
                $selectOperator = ' NOT IN(?)';
                break;

            default:
                $selectOperator = '=?';
                break;
        }

        return $this->_getReadAdapter()->quoteInto("{$field}{$selectOperator}", $value);
    }

    public function getOperatorBindCondition($field, $attribute, $operator, &$bind, $callback = array()) {
        $bindName = ':targetrule_bind_' . $this->_bindIncrement ++;
        switch ($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':
                $condition = sprintf('%s%s%s', $field, $operator, $bindName);
                break;
            case '{}':
                $condition  = sprintf('%s LIKE %s', $field, $bindName);
                $callback[] = 'bindLikeValue';
                break;

            case '!{}':
                $condition  = sprintf('%s NOT LIKE %s', $field, $bindName);
                $callback[] = 'bindLikeValue';
                break;

            case '()':
                $condition = sprintf('FIND_IN_SET(%s, %s)', $field, $bindName);
                break;

            case '!()':
                $condition = sprintf('FIND_IN_SET(%s, %s) IS NULL', $field, $bindName);
                break;

            default:
                $condition = sprintf('%s=%s', $field, $bindName);
                break;
        }

        $bind[] = array(
            'bind'      => $bindName,
            'field'     => $attribute,
            'callback'  => $callback
        );

        return $condition;
    }

    public function bindLikeValue($value) {
        return '%' . $value . '%';
    }

    public function bindArrayOfIds($value) {
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $value = array_map('trim', $value);
        $value = array_filter($value, 'is_numeric');

        return $value;
    }

    public function bindPercentOf($value, $percent) {
        return round($value * ($percent / 100), 4);
    }

    public function cleanIndex($typeId = null, $store = null) {
        $adapter = $this->_getWriteAdapter();

        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }

        if (is_null($typeId)) {
            foreach ($this->getTypeIds() as $typeId) {
                $this->getTypeIndex($typeId)->cleanIndex($store);
            }
            if (is_null($store)) {
                $adapter->truncate($this->getMainTable());
            } else {
                $where = array('store_id IN(?)' => $store);
                $adapter->delete($this->getMainTable(), $where);
            }
        } else {
            $where = array('type_id=?' => $typeId);
            if (!is_null($store)) {
                $where['store_id IN(?)'] = $store;
            }
            $adapter->delete($this->getMainTable(), $where);
            $this->getTypeIndex($typeId)->cleanIndex($store);
        }

        return $this;
    }

    public function removeIndexByProductIds($productIds, $typeId = null) {
        $adapter = $this->_getWriteAdapter();

        $where = array(
            'entity_id IN(?)'   => $productIds
        );

        if (is_null($typeId)) {
            foreach ($this->getTypeIds() as $typeId) {
                $this->getTypeIndex($typeId)->removeIndex($productIds);
            }
        } else {
            $this->getTypeIndex($typeId)->removeIndex($productIds);
            $where['type_id=?'] = $typeId;
        }

        $adapter->delete($this->getMainTable(), $where);
    }

    public function removeProductIndex($productId = null, $ruleId = null) {
        $adapter = $this->_getWriteAdapter();
        $where   = array();
        if (!is_null($productId)) {
            $where['product_id=?'] = $productId;
        }
        if (!is_null($ruleId)) {
            $where['rule_id=?'] = $ruleId;
        }

        $adapter->delete($this->getTable('evogue_targetrule/product'), $where);

        return $this;
    }

    public function saveProductIndex($ruleId, $productId) {
        $this->removeProductIndex($productId, $ruleId);

        $adapter = $this->_getWriteAdapter();
        $bind    = array(
            'rule_id'       => $ruleId,
            'product_id'    => $productId
        );

        $adapter->insert($this->getTable('evogue_targetrule/product'), $bind);

        return $this;
    }
}
