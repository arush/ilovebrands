<?php
class Evogue_TargetRule_Adminhtml_TargetRuleController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu('catalog/targetrule');
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Rule-based Product Relations'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Rule-based Product Relations'));

        $model  = Mage::getModel('evogue_targetrule/rule');
        $ruleId = $this->getRequest()->getParam('id', null);

        if ($ruleId) {
            $model->load($ruleId);
            if (!$model->getId()) {
                $this->_getSession()->addError(Mage::helper('evogue_targetrule')->__('This rule no longer exists'));
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Rule'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_target_rule', $model);

        $block = $this->getLayout()->createBlock('evogue_targetrule/adminhtml_targetrule_edit');
        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);

        $this
            ->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('evogue_targetrule/adminhtml_targetrule_edit_tabs'))
            ->renderLayout();
    }

    public function newConditionHtmlAction() {
        $this->conditionsHtmlAction('conditions');
    }

    public function newActionsHtmlAction() {
        $this->conditionsHtmlAction('actions');
    }

    public function saveAction() {
        $redirectPath   = '*/*/';
        $redirectParams = array();

        $data = $this->getRequest()->getPost();
        $data = $this->_filterDates($data, array('from_date', 'to_date'));

        if ($this->getRequest()->isPost() && $data) {
            $model          = Mage::getModel('evogue_targetrule/rule');
            $redirectBack   = $this->getRequest()->getParam('back', false);
            $hasError       = false;

            try {
                $ruleId = $this->getRequest()->getParam('rule_id');
                if ($ruleId) {
                    $model->load($ruleId);
                    if ($ruleId != $model->getId()) {
                        Mage::throwException(Mage::helper('evogue_targetrule')->__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validate(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setFormData($data);

                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                $data['actions']    = $data['rule']['actions'];
                unset($data['rule']);

                $model->loadPost($data);
                $model->save();

                $this->_getSession()->addSuccess(
                    Mage::helper('evogue_targetrule')->__('The rule has been saved.')
                );

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $model->getId(),
                        '_current' => true,
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('evogue_targetrule')->__('An error occurred while saving Product Rule.')
                );

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
            }

            if ($hasError || $redirectBack) {
                $redirectPath = '*/*/edit';
                $redirectParams['id'] = $model->getId();
            }
        }
        $this->_redirect($redirectPath, $redirectParams);
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('evogue_targetrule/rule');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('evogue_targetrule')->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('evogue_targetrule')->__('Unable to find a page to delete'));
        $this->_redirect('*/*/');
    }

    protected function conditionsHtmlAction($prefix) {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('evogue_targetrule/rule'))
            ->setPrefix($prefix);
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

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/targetrule');
    }
}
