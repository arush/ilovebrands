<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
require_once ('app/code/community/TBT/Rewardsinstore/controllers/LoyaltyController.php');
class TBT_Rewardsinstore_Manage_Earning_CartController extends TBT_Rewardsinstore_LoyaltyController
{
    protected function _construct ()
    {
        $this->_usedModuleName = 'rewardsinstore';
        parent::_construct();
    }
    
    protected function _initRule ()
    {
        Mage::register('current_promo_quote_rule', Mage::getModel('rewardsinstore/salesrule_rule'));
        if ($id = (int) $this->getRequest()->getParam('id')) {
            Mage::registry('current_promo_quote_rule')->load($id);
        }
    }
    
    protected function _initAction ()
    {
        $this->loadLayout()->_setActiveMenu('rewards/rules')->_addBreadcrumb(Mage::helper('rewardsinstore')->__('Instore Rewards'), Mage::helper('rewards')->__('Instore Rewards'));
        return $this;
    }
    
    public function indexAction ()
    {
        // redemption type (1) or distribution type (2)
        $type = $this->getRequest()->getParam('type');
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('rewards')
            ->__('Catalog'), Mage::helper('rewards')->__('Catalog'));
        
        // TODO: add instore type
        if (Mage::helper('rewards/rule_type')->isRedemption($type)) {
            $this->_addContent($this->getLayout()->createBlock('rewardsinstore/manage_earning_cart_redemptions'));
        } else {
            $this->_addContent($this->getLayout()->createBlock('rewardsinstore/manage_earning_cart_distributions'));
        }
        $this->renderLayout();
    }
    
    public function newAction ()
    {
        $this->editAction();
    }
    
    public function editAction ()
    {
        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type'); // redemption or distribution
        $model = Mage::getModel('rewardsinstore/cartrule');
        
        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewards')->__('This rule no longer exists'));
                $this->_redirect('*/*', array('type' => $type));
                return;
            }
            $type = $model->getRuleTypeId();
        }
        
        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (! empty($data)) {
            $model->addData($data);
        }
        
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');
        
        // TODO: replace this string with current_earning_cart_rule
        Mage::register('current_promo_quote_rule', $model);
        $block = $this->getLayout()
            ->createBlock('rewardsinstore/manage_earning_cart_edit')
            ->setData('action', $this->getUrl('*/*/save', array('type' => $type)));
            
        $this->_initAction();
        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);
        
        $this->_addBreadcrumb($id ? Mage::helper('rewards')->__('Edit Rule') : Mage::helper('rewards')->__('New Rule'), $id ? Mage::helper('rewards')->__('Edit Rule') : Mage::helper('rewards')->__('New Rule'))->_addContent($block)->_addLeft($this->getLayout()->createBlock('rewardsinstore/manage_earning_cart_edit_tabs'))->renderLayout();
    }
    
    public function saveAction ()
    {
        $type = $this->getRequest()->getParam('type');
        
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('rewardsinstore/cartrule');
            if (Mage::helper('rewards')->isBaseMageVersionAtLeast('1.4')) {
                $data = $this->_filterDates($data, array('from_date' , 'to_date'));
                $session = Mage::getSingleton('adminhtml/session');
                $validateResult = $model->validateData(new Varien_Object($data));
                
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/', array('type' => $model->getRuleTypeId() , 'id' => $model->getId()));
                    return;
                }
            }
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent' && isset($data['discount_amount'])) {
                $data['discount_amount'] = min(100, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);
            try {
                $model->loadPost($data);
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $type = $model->getRuleTypeId(); // redemption type or distribution type
                $model->save();
                $type = $model->getRuleTypeId(); // redemption type or distribution type (again in case it changed)
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewards')->__('Rule was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                $this->_redirect('*/*/', array('type' => $type , 'id' => $model->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id') , 'type' => $type));
                return;
            }
        }
        
        $this->_redirect('*/*/');
    }
    
    public function deleteAction ()
    {
        $type = $this->getRequest()->getParam('type'); // redemption type or distribution type
        $id = $this->getRequest()->getParam('id');
        try {
            $model = Mage::getModel('rewardsinstore/cartrule');
            if (! $id) {
                throw new Exception('No rule specified, so the rule could not be deleted.');
            }
            $model->load($id);
            if (! $model->getId()) {
                throw new Exception(Mage::helper('rewards')->__('The rule you are trying to delete no longer exists'));
            }
            $id = $model->getId();
            $type = $model->getRuleTypeId(); // redemption type or distribution type
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewards')->__('Rule was successfully deleted'));
            $this->_redirect('*/*/', array('type' => $type));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setPageData($this->getRequest()->getPost());
            $this->_redirect('*/*/edit', array('id' => $id , 'type' => $type));
            return;
        }
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('salesrule/rule'))
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
    
    public function newActionHtmlAction ()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', (string) $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = Mage::getModel($type)->setId($id)->setType($type)->setRule(Mage::getModel('salesrule/rule'))->setPrefix('actions');
        if (! empty($typeArr[1])) {
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
    
    public function applyRulesAction ()
    {
        $this->_initAction();
        $this->renderLayout();
    }
    
    public function gridAction ()
    {
        $this->_initRule();
        $this->getResponse()->setBody($this->getLayout()->createBlock('rewards/manage_promo_quote_edit_tab_product')->toHtml());
    }
    
    protected function _isAllowed ()
    {
        // TODO: make this more granular (eg. rewards/rules/instoredistribution/instorebehavior)... but how?
        return Mage::getSingleton('admin/session')->isAllowed('rewards/rules');
    }
    
    public function preDispatch ()
    {
        if (! Mage::helper('rewards/loyalty_checker')->isValid()) {
            Mage::throwException("Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem.");
        }
        parent::preDispatch();
    }
    //    protected function _initAction() {
//        // load layout, set active menu and breadcrumbs
//        $this->loadLayout()
//            ->_setActiveMenu('rewards/instore')
//            ->_addBreadcrumb(Mage::helper('rewards')->__('Rewards'), Mage::helper('rewards')->__('Rewards'))
//            ->_addBreadcrumb(Mage::helper('rewardsinstore')->__('Cart Rule'), Mage::helper('rewardsinstore')->__('Cart Rule'));
//            
//        return $this;
//    }
//    
//    public function indexAction() {
//        $this->loadLayout();
//        $this->renderLayout();
//    }
//    
//    public function newAction() {
//        echo 'newAction';
//    }
}
