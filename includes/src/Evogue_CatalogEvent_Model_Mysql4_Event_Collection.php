<?php

class Evogue_CatalogEvent_Model_Mysql4_Event_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_categoryDataAdded = false;

    protected $_skipClosed = false;

    protected function _construct() {
        $this->_init('evogue_catalogevent/event');
    }

    public function addFieldToFilter($field, $condition = null) {
        if ($field == 'display_state') {
            $field = $this->_getMappedField($field);
            if (is_array($condition) && isset($condition['eq'])) {
                $condition = $condition['eq'];
            }
            $this->_select->where('(' . $field . ' & ' . (int) $condition . ') = ' . (int) $condition);
            return $this;
        }
        if ($field == 'status') {
            $this->getSelect()->where($this->_getConditionSql($this->_getStatusColumnExpr(), $condition));
            return $this;
        }
        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    public function addVisibilityFilter() {
        $this->_skipClosed = true;
        $this->addFieldToFilter('status', array(
            'nin' => Evogue_CatalogEvent_Model_Event::STATUS_CLOSED
        ));
        return $this;
    }

    protected function _setOrder($field, $direction, $unshift = false) {
        if ($field == 'category_name' && $this->_categoryDataAdded) {
            $field = 'category_position';
        }
        return parent::_setOrder($field, $direction, $unshift);
    }

    public function addCategoryData() {
        if (!$this->_categoryDataAdded) {
             $this->getSelect()
                ->joinLeft(array('category' => $this->getTable('catalog/category')), 'category.entity_id = main_table.category_id', array('category_position' => 'position'))
                ->joinLeft(array('category_name_attribute' => $this->getTable('eav/attribute')), 'category_name_attribute.entity_type_id = category.entity_type_id
                    AND category_name_attribute.attribute_code = \'name\'', array())
                ->joinLeft(array('category_varchar' => $this->getTable('catalog/category') . '_varchar'), 'category_varchar.entity_id = category.entity_id
                    AND category_varchar.attribute_id = category_name_attribute.attribute_id
                    AND category_varchar.store_id = 0
                ', array('category_name' => 'value'));
            $this->_map['fields']['category_name'] = 'category_varchar.value';
            $this->_map['fields']['category_position'] = 'category.position';
            $this->_categoryDataAdded = true;
        }
        return $this;
    }

    public function addSortByStatus() {
        $this->getSelect()
            ->order(array(
                $this->getConnection()->quoteInto(
                    'IF (' . $this->_getStatusColumnExpr() . ' = ?, 0, 1) ASC',
                    Evogue_CatalogEvent_Model_Event::STATUS_OPEN
                ),
                $this->getConnection()->quoteInto(
                    'IF (' . $this->_getStatusColumnExpr() . ' = ?, main_table.date_end, main_table.date_start) ASC',
                    Evogue_CatalogEvent_Model_Event::STATUS_OPEN
                ),
                'main_table.sort_order ASC'
        ));

        return $this;
    }

    public function addImageData() {
        $this->getSelect()->joinLeft(
            array('event_image' => $this->getTable('event_image')),
            'event_image.event_id = main_table.event_id
            AND event_image.store_id = ' . Mage::app()->getStore()->getId() . '',
            array('image' => 'IFNULL(event_image.image, event_image_default.image)')
        )
        ->joinLeft(
            array('event_image_default' => $this->getTable('event_image')),
            'event_image_default.event_id = main_table.event_id
            AND event_image_default.store_id = 0',
            array())
        ->group('main_table.event_id');

        return $this;
    }

    public function capByCategoryPaths($allowedPaths) {
        $this->addCategoryData();
        $paths = array();
        foreach ($allowedPaths as $path) {
            $paths[] = $this->getConnection()->quoteInto('category.path = ?', $path);
            $paths[] = $this->getConnection()->quoteInto('category.path LIKE ?', $path . '/%');
        }
        if ($paths) {
            $this->getSelect()->where(implode(' OR ', $paths));
        }
        return $this;
    }

    protected function _afterLoad() {
        $events = parent::_afterLoad();
        foreach ($events->_items as $event) {
            if ($this->_skipClosed && $event->getStatus() == Evogue_CatalogEvent_Model_Event::STATUS_CLOSED) {
                $this->removeItemByKey($event->getId());
            }
        }
        return $this;
    }

    protected function _reset() {
        $this->_skipClosed = false;
        return parent::_reset();
    }

    protected function _getStatusColumnExpr() {
        $timeNow = $this->getResource()->formatDate(time());
        $connection = $this->getConnection();
        $timeNowQuoted = $connection->quote($timeNow);
        return new Zend_Db_Expr(vsprintf('(CASE WHEN (`date_start` <= %s AND `date_end` >= %s) THEN %s'
        . ' WHEN (`date_start` > %s AND `date_end` > %s) THEN %s ELSE %s END)', array(
            $timeNowQuoted,
            $timeNowQuoted,
            $connection->quote(Evogue_CatalogEvent_Model_Event::STATUS_OPEN),
            $timeNowQuoted,
            $timeNowQuoted,
            $connection->quote(Evogue_CatalogEvent_Model_Event::STATUS_UPCOMING),
            $connection->quote(Evogue_CatalogEvent_Model_Event::STATUS_CLOSED)
        )));
    }

    protected function _initSelect() {
        parent::_initSelect();
        $this->getSelect()->columns(array('status' => $this->_getStatusColumnExpr()));
        return $this;
    }
}
