<?php
class Evogue_CatalogEvent_Block_Event_Lister extends Evogue_CatalogEvent_Block_Event_Abstract {

    protected $_events = null;

    public function getHtmlId() {
        if (!$this->hasData('html_id')) {
            $this->setData('html_id', 'id_' . md5(uniqid('catalogevent', true)));
        }

        return $this->getData('html_id');
    }

    public function canDisplay() {
        return Mage::helper('evogue_catalogevent')->isEnabled()
            && Mage::getStoreConfigFlag('catalog/evogue_catalogevent/lister_output')
            && (count($this->getEvents()) > 0);
    }

    public function getEvents() {
        if ($this->_events === null) {
            $this->_events = array();
            $categories = $this->helper('catalog/category')->getStoreCategories('position', true, false);
            if (($categories instanceof Mage_Eav_Model_Entity_Collection_Abstract) ||
                ($categories instanceof Mage_Core_Model_Mysql4_Collection_Abstract)) {
                $allIds = $categories->getAllIds();
            } else {
                $allIds = array();
            }

            if (!empty($allIds)) {
                $eventCollection = Mage::getModel('evogue_catalogevent/event')
                    ->getCollection();
                $eventCollection->addFieldToFilter('category_id', array('in' => $allIds))
                    ->addVisibilityFilter()
                    ->addImageData()
                    ->addSortByStatus()
                ;

                $categories->addIdFilter(
                    $eventCollection->getColumnValues('category_id')
                );

                foreach ($categories as $category) {
                    $event = $eventCollection->getItemByColumnValue('category_id', $category->getId());
                    if ($category->getIsActive()) {
                        $event->setCategory($category);
                    } else {
                        $eventCollection->removeItemByKey($event->getId());
                    }
                }

                foreach ($eventCollection as $event) {
                    $this->_events[] = $event;
                }


            }
        }

        return $this->_events;
    }

    public function getCategoryUrl($category) {
        return $this->helper('catalog/category')->getCategoryUrl($category);
    }

    public function getEventImageUrl($event) {
        return $this->helper('evogue_catalogevent')->getEventImageUrl($event);
    }

    public function getPageSize() {
        if ($this->hasData('limit') && is_numeric($this->getData('limit'))) {
            $pageSize = (int) $this->_getData('limit');
        } else {
            $pageSize = (int)Mage::getStoreConfig('catalog/evogue_catalogevent/lister_widget_limit');
        }
        return max($pageSize, 1);
    }

    public function getScrollSize() {
        if ($this->hasData('scroll') && is_numeric($this->getData('scroll'))) {
            $scrollSize = (int) $this->_getData('scroll');
        } else {
            $scrollSize = (int)Mage::getStoreConfig('catalog/evogue_catalogevent/lister_widget_scroll');
        }
        return  min(max($scrollSize, 1), $this->getPageSize());
    }

    protected function _toHtml() {
        if (!$this->canDisplay()) {
            return '';
        }
        return parent::_toHtml();
    }
}
