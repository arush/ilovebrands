<?php
class EVogue_CatalogEvent_Model_Mysql4_Event extends Mage_Core_Model_Mysql4_Abstract {

    const EVENT_FROM_PARENT_FIRST = 1;
    const EVENT_FROM_PARENT_LAST  = 2;

    protected $_childToParentList;

    protected $_eventCategories;

    protected function _construct() {
        $this->_init('evogue_catalogevent/event', 'event_id');
        $this->addUniqueField(array('field' => 'category_id' , 'title' => Mage::helper('evogue_catalogevent')->__('Event for selected category')));
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if (strlen($object->getSortOrder()) === 0) {
            $object->setSortOrder(null);
        }

        return parent::_beforeSave($object);
    }

    public function getCategoryIdsWithEvent($storeId = null) {
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

        /* @var $select Varien_Db_Select */
        $select = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId(Mage::app()->getStore($storeId)->getId())
            ->addIsActiveFilter()
            ->addPathsFilter(Mage_Catalog_Model_Category::TREE_ROOT_ID . '/' . $rootCategoryId)
            ->getSelect();

        $parts = $select->getPart(Zend_Db_Select::FROM);

        if (isset($parts['main_table'])) {
            $categoryCorrelationName = 'main_table';
        } else {
            $categoryCorrelationName = 'e';

        }

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array('entity_id','level', 'path'), $categoryCorrelationName);

        $select
            ->joinLeft(
                array('event'=>$this->getMainTable()),
                'event.category_id = ' . $categoryCorrelationName . '.entity_id',
                'event_id'
        )->order($categoryCorrelationName . '.level ASC');

        $this->_eventCategories = $this->_getReadAdapter()->fetchAssoc($select);

        if (empty($this->_eventCategories)) {
            return array();
        }
        $this->_setChildToParentList();

        foreach ($this->_eventCategories as $categoryId => $category){
            if ($category['event_id'] === null && isset($category['level']) && $category['level'] > 2) {
                $result[$categoryId] = $this->_getEventFromParent($categoryId, self::EVENT_FROM_PARENT_LAST);
            } elseif  ($category['event_id'] !== null) {
                $result[$categoryId] = $category['event_id'];
            } else {
                $result[$categoryId] = null;
            }
        }

        return $result;
    }

    protected function _setChildToParentList() {
        if (is_array($this->_eventCategories)) {
            foreach ($this->_eventCategories as $row) {
                $category = explode('/', $row['path']);
                $amount = count($category);
                if ($amount > 2) {
                    $key = $category[$amount-1];
                    $val = $category[$amount-2];
                    if (empty($this->_childToParentList[$key])) {
                        $this->_childToParentList[$key] = $val;
                    }
                }
            }
        }
        return $this;
    }

    protected function _getEventFromParent($categoryId, $flag =2) {
        if (isset($this->_childToParentList[$categoryId])) {
            $parentId = $this->_childToParentList[$categoryId];
        }
        if (!isset($parentId)) {
            return null;
        }
        $eventId = null;
        if (isset($this->_eventCategories[$parentId])) {
            $eventId = $this->_eventCategories[$parentId]['event_id'];
        }
        if ($flag == self::EVENT_FROM_PARENT_LAST){
            if (isset($eventId) && ($eventId !== null)) {
                return $eventId;
            }
            elseif ($eventId === null) {
                return $this->_getEventFromParent($parentId, $flag);
            }
        }
        return null;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $this->_getWriteAdapter()->delete(
            $this->getTable('event_image'),
             $object->getIdFieldName() . ' = ' . $object->getId() .
            ' AND store_id = ' . $object->getStoreId()
        );

        if ($object->getImage() !== null) {
            $this->_getWriteAdapter()->insert(
                $this->getTable('event_image'),
                array(
                    $object->getIdFieldName() => $object->getId(),
                    'store_id' => $object->getStoreId(),
                    'image' => $object->getImage()
                )
            );
        }
        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('event_image'), array(
                'type' => 'IF(store_id = 0, \'default\', \'store\')',
                'image'
            ))
            ->where($object->getIdFieldName() . ' = ?', $object->getId())
            ->where('store_id IN (0,?)', $object->getStoreId());

        $images = $this->_getReadAdapter()->fetchPairs($select);

        if (isset($images['store'])) {
            $object->setImage($images['store']);
            $object->setImageDefault(isset($images['default']) ? $images['default'] : '');
        }

        if (isset($images['default']) && !isset($images['store'])) {
            $object->setImage($images['default']);
        }

        return parent::_afterLoad($object);
    }
}
