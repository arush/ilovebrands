<?php
class Evogue_CatalogEvent_Block_Adminhtml_Catalog_Category_Edit_Buttons extends Mage_Adminhtml_Block_Catalog_Category_Abstract {
    public function getEvent() {
        if (!$this->hasData('event')) {
            $collection = Mage::getModel('evogue_catalogevent/event')->getCollection()
                ->addFieldToFilter('category_id', $this->getCategoryId());

            $event = $collection->getFirstItem();
            $this->setData('event', $event);
        }

        return $this->getData('event');
    }

    public function addButtons() {
        if ($this->helper('evogue_catalogevent')->isEnabled() &&
            Mage::getSingleton('admin/session')->isAllowed('catalog/events') &&
            $this->getCategoryId() && $this->getCategory()->getLevel() > 1) {
            if ($this->getEvent() && $this->getEvent()->getId()) {
                $url = $this->helper('adminhtml')->getUrl('*/catalog_event/edit', array(
                            'id' => $this->getEvent()->getId(),
                            'category' => 1
                ));
                $this->getParentBlock()->getChild('form')
                    ->addAdditionalButton('edit_event', array(
                        'label' => $this->helper('evogue_catalogevent')->__('Edit Event...'),
                        'class' => 'save',
                        'onclick'   => 'setLocation(\''. $url .'\')'
                    ));
            } else {
                $url = $this->helper('adminhtml')->getUrl('*/catalog_event/new', array(
                        'category_id' => $this->getCategoryId(),
                        'category' => 1
                ));
                $this->getParentBlock()->getChild('form')
                    ->addAdditionalButton('add_event', array(
                        'label' => $this->helper('evogue_catalogevent')->__('Add Event...'),
                        'class' => 'add',
                        'onclick' => 'setLocation(\''. $url .'\')'
                    ));
            }
        }
        return $this;
    }
}
