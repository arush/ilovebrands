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
class TBT_Rewardsinstore_Manage_StorefrontController extends TBT_Rewardsinstore_LoyaltyController
{
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('rewards/instore')
            ->_addBreadcrumb(Mage::helper('rewards')->__('Rewards'), Mage::helper('rewards')->__('Rewards'))
            ->_addBreadcrumb(Mage::helper('rewardsinstore')->__('Manage Instore'), Mage::helper('rewardsinstore')->__('Manage Instore'));
            
        return $this;
    }
    
    /**
     * Acl permissions
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewards/instore');
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function newAction()
    {
        Mage::register('storefront_action', 'add');
        $this->_forward('edit');
    }
    
    public function editAction()
    {
        $this->_title($this->__('Rewards'))
             ->_title($this->__('Storefront'));
        
        $session = $this->_getSession();
        if ($session->getPostData()) {
            Mage::register('storefront_post_data', $session->getPostData());
            $session->unsPostData();
        }
             
        if (!Mage::registry('storefront_action')) {
            Mage::register('storefront_action', 'edit');
        }
        
        // Storefront data
        $itemId     = $this->getRequest()->getParam('id', null);
        $model      = Mage::getModel('rewardsinstore/storefront');
        $title      = Mage::helper('rewardsinstore')->__("Storefront");
        $notExists  = Mage::helper('rewardsinstore')->__("The storefront does not exist");
        
        if (null !== $itemId) {
            $model->load($itemId);
        }
        
        if ($model->getId() != null || Mage::registry('storefront_action') == 'add') {
            Mage::register('storefront_data', $model);
            
            if (Mage::registry('storefront_action') == 'add') {
                $this->_title($this->__('New ') . $title);
            }
            else {
                $this->_title($model->getName());
            }
            
            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('rewardsinstore/manage_storefront_edit'))
                ->renderLayout();
        }
        else {
            $session->addError($notExists);
            $this->_redirect('*/*/');
        }
    }
    
    public function saveAction()
    {
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPost()) {
            if (empty($postData['storefront_action'])) {
                $this->_redirect('*/*/');
                return;
            }
            $session = $this->_getSession();
            
            try {
                
                $storefrontModel = Mage::getModel('rewardsinstore/storefront');
                if ($postData['storefront']['storefront_id']) {
                    $storefrontModel->load($postData['storefront']['storefront_id']);
                }
                $storefrontModel->setData($postData['storefront']);
                if ($postData['storefront']['storefront_id'] == '') {
                    $storefrontModel->setId(null);
                }
                
                $storefrontModel->save();
                
                Mage::dispatchEvent('storefront_save', array('storefront' => $storefrontModel));
                
                $session->addSuccess(Mage::helper('rewardsinstore')->__('The storefront has been saved.'));
                
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $session->setPostData($postData);
            }
            catch (Exception $e) {
                $session->addException($e, Mage::helper('core')->__('An error occurred while saving. Please review the error log.'));
                $session->setPostData($postData);
            }
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Storefronts'))
             ->_title($this->__('Delete Storefront'));
        
        $session = $this->_getSession();
        
        try {
        
            $itemId = $this->getRequest()->getParam('id', null);
            if (!$model = Mage::getModel('rewardsinstore/storefront')->load($itemId)) {
                $session->addError(Mage::helper('core')->__('Unable to proceed. Please, try again.'));
                $this->_redirect('*/*/');
                return;
            }
            
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('rewardsinstore')->__('Storefront was successfully deleted'));
            $this->_redirect('*/*/');
            return;
        
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('rewardsinstore')->__('An error occurred while deleting. Please review the error log.'));
        }
        $this->_redirectReferer();
        return;
    }
}
