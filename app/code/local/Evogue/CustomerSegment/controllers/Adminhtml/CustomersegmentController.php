<?php
class Evogue_CustomerSegment_Adminhtml_CustomersegmentController extends Mage_Adminhtml_Controller_Action {

    protected function _initSegment($requestParam = 'id', $requireValidId = false) {
        $segmentId = $this->getRequest()->getParam($requestParam, 0);
        $segment = Mage::getModel('evogue_customersegment/segment');
        if ($segmentId || $requireValidId) {
            $segment->load($segmentId);
            if (!$segment->getId()) {
                Mage::throwException($this->__('Wrong customer segment requested.'));
            }
        }
        Mage::register('current_customer_segment', $segment);
        return $segment;
    }

    public function indexAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Segments'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/customersegment');
        $this->renderLayout();
    }

    public function newAction() {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Segments'));

        try {
            $model = $this->_initSegment();
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Segment'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('segment_conditions_fieldset');

        $block =  $this->getLayout()->createBlock('evogue_customersegment/adminhtml_customersegment_edit')
            ->setData('form_action_url', $this->getUrl('*/*/save'));

        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);

        $this->_addBreadcrumb(
                $model->getId() ? $this->__('Edit Segment') : $this->__('New Segment'),
                $model->getId() ? $this->__('Edit Segment') : $this->__('New Segment'))
            ->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('evogue_customersegment/adminhtml_customersegment_edit_tabs'))
            ->renderLayout();
    }

    public function matchAction() {
        try {
            $model = $this->_initSegment();
            $model->matchCustomers();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('evogue_customersegment')->__('Segment Customers matching error.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $this->_redirect('*/*/edit', array('id' => $model->getId(), 'active_tab'=>'customers_tab'));
    }

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('customer/customersegment')
            ->_addBreadcrumb(Mage::helper('evogue_customersegment')->__('Segments'), Mage::helper('evogue_customersegment')->__('Segments'))
        ;
        return $this;
    }

    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('evogue_customersegment/segment'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);

                $model = $this->_initSegment('segment_id');
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $model->save();
                $model->matchCustomers();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The segment has been saved.'));
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $model->getId(),
                        '_current' => true,
                    ));
                    return;
                }

            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('segment_id')));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to save the segment.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        try {
            $model = $this->_initSegment('id', true);
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The segment has been deleted.'));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to delete the segment.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/customersegment') &&
            Mage::helper('evogue_customersegment')->isEnabled();
    }

    public function chooserDaterangeAction() {
        $block = $this->getLayout()->createBlock('adminhtml/promo_widget_chooser_daterange');
        if ($block) {
            // set block data from request
            $block->setTargetElementId($this->getRequest()->getParam('value_element_id'));
            $selectedValues = $this->getRequest()->getParam('selected');
            if (!empty($selectedValues) && is_array($selectedValues) && 1 === count($selectedValues)) {
                $block->setRangeValue(array_shift($selectedValues));
            }

            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function chooserGridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
