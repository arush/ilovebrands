<?php
class Evogue_CatalogEvent_Adminhtml_Catalog_EventController extends Mage_Adminhtml_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();
        if (!Mage::helper('evogue_catalogevent')->isEnabled()) {
            if ($this->getRequest()->getActionName() != 'noroute') {
                $this->_forward('noroute');
            }
        }
        return $this;
    }

    public function _initAction() {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('evogue_catalogevent')->__('Events'), Mage::helper('evogue_catalogevent')->__('Events'))
            ->_setActiveMenu('catalog/evogue_catalogevent');
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Catalog Events'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Catalog Events'));

        $event = Mage::getModel('evogue_catalogevent/event')
            ->setStoreId($this->getRequest()->getParam('store', 0));
        if ($eventId = $this->getRequest()->getParam('id', false)) {
            $event->load($eventId);
        } else {
            $event->setCategoryId($this->getRequest()->getParam('category_id'));
        }

        $this->_title($event->getId() ? sprintf("#%s", $event->getId()) : $this->__('New Event'));

        $sessionData = Mage::getSingleton('adminhtml/session')->getEventData(true);
        if (!empty($sessionData)) {
            $event->addData($sessionData);
        }

        Mage::register('evogue_catalogevent_event', $event);

        $this->_initAction();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        if (($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            if (!$event->getId() || Mage::app()->isSingleStoreMode()) {
                $switchBlock->getParentBlock()->unsetChild('store_switcher');
            } else {
                $switchBlock->setDefaultStoreName(Mage::helper('evogue_catalogevent')->__('Default Values'))
                    ->setSwitchUrl($this->getUrl('*/*/*', array('_current'=>true, 'store'=>null)));
            }
        }
        $this->renderLayout();

    }

    public function saveAction() {
        $event = Mage::getModel('evogue_catalogevent/event')
            ->setStoreId($this->getRequest()->getParam('store', 0));
        if ($eventId = $this->getRequest()->getParam('id', false)) {
            $event->load($eventId);
        } else {
            $event->setCategoryId($this->getRequest()->getParam('category_id'));
        }

        $postData = $this->_filterPostData($this->getRequest()->getPost());

        if (!isset($postData['catalogevent'])) {
            $this->_getSession()->addError(
                Mage::helper('evogue_catalogevent')->__('An error occurred while saving this event.')
            );
            $this->_redirect('*/*/edit', array('_current'=>true));
            return;
        }

        $data = new Varien_Object($postData['catalogevent']);

        $event->setDisplayState($data->getDisplayState())
            ->setStoreDateStart($data->getDateStart())
            ->setStoreDateEnd($data->getDateEnd())
            ->setSortOrder($data->getSortOrder());

        $isUploaded = true;
        try {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $uploader->setFilesDispersion(false);
        } catch (Exception $e) {
            $isUploaded = false;
        }

        $validateResult = $event->validate();
        if ($validateResult !== true) {
            foreach ($validateResult as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $this->_getSession()->setEventData($event->getData());
            $this->_redirect('*/*/edit', array('_current'=>true));
            return;
        }

        try {
            if ($data->getData('image/is_default')) {
                $event->setImage(null);
            } elseif ($data->getData('image/delete')) {
                $event->setImage('');
            } elseif ($isUploaded) {
                try {
                    $event->setImage($uploader);
                } catch (Exception $e) {
                    Mage::throwException(
                        Mage::helper('evogue_catalogevent')->__('Image was not uploaded.')
                    );
                }
            }
            $event->save();

            $this->_getSession()->addSuccess(
                Mage::helper('evogue_catalogevent')->__('Event has been saved.')
            );
            if ($this->getRequest()->getParam('_continue')) {
                $this->_redirect('*/*/edit', array('_current'=>true, 'id'=>$event->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setEventData($event->getData());
            $this->_redirect('*/*/edit', array('_current'=>true));
        }


    }

    public function deleteAction() {
        $event = Mage::getModel('evogue_catalogevent/event');
        $event->load($this->getRequest()->getParam('id', false));
        if ($event->getId()) {
            try {
                $event->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('evogue_catalogevent')->__('Event has been deleted.')
                );
                if ($this->getRequest()->getParam('category')) {
                    $this->_redirect('*/catalog_category/edit', array('id' => $event->getCategoryId(), 'clear' => 1));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('_current'=>true));
            }
        }
    }

    public function categoriesJsonAction() {
        $id = $this->getRequest()->getParam('id', null);
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('evogue_catalogevent/adminhtml_event_edit_category')
                ->getTreeArray($id, true, 1)
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/events');
    }

    protected function _filterPostData($data) {
        if(isset($data['catalogevent'])) {
            $_data = $data['catalogevent'];
            $_data = $this->_filterDateTime($_data, array('date_start', 'date_end'));
            $data['catalogevent'] = $_data;
        }
        return $data;
    }

}
