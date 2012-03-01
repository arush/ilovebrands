<?php
class Evogue_Customer_Adminhtml_Customer_AttributeController
    extends Mage_Adminhtml_Controller_Action {

    protected $_entityType;


    protected function _getEntityType() {
        if (is_null($this->_entityType)) {
            $this->_entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        }
        return $this->_entityType;
    }

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('customer/attributes')
            ->_addBreadcrumb(
                Mage::helper('evogue_customer')->__('Customer'),
                Mage::helper('evogue_customer')->__('Customer'))
            ->_addBreadcrumb(
                Mage::helper('evogue_customer')->__('Manage Customer Attributes'),
                Mage::helper('evogue_customer')->__('Manage Customer Attributes'));
        return $this;
    }

    protected function _initAttribute() {
        $attribute = Mage::getModel('customer/attribute');
        $websiteId = $this->getRequest()->getParam('website');
        if ($websiteId) {
            $attribute->setWebsite($websiteId);
        }
        return $attribute;
    }

    public function indexAction() {
        $this->_title($this->__('Manage Customer Attributes'));
        $this->_initAction()
            ->renderLayout();
    }

    public function newAction() {
        $this->addActionLayoutHandles();
        $this->_forward('edit');
    }

    public function editAction() {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attributeObject = $this->_initAttribute()
            ->setEntityTypeId($this->_getEntityType()->getId());

        $this->_title($this->__('Manage Customer Attributes'));

        if ($attributeId) {
            $attributeObject->load($attributeId);
            if (!$attributeObject->getId()) {
                $this->_getSession()
                    ->addError(Mage::helper('evogue_customer')->__('Attribute is no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                $this->_getSession()->addError(Mage::helper('evogue_customer')->__('You cannot edit this attribute.'));
                $this->_redirect('*/*/');
                return;
            }

            $this->_title($attributeObject->getFrontendLabel());
        } else {
            $this->_title($this->__('New Attribute'));
        }

        $attributeData = $this->_getSession()->getAttributeData(true);
        if (!empty($attributeData)) {
            $attributeObject->setData($attributeData);
        }
        Mage::register('entity_attribute', $attributeObject);

        $label = $attributeObject->getId()
            ? Mage::helper('evogue_customer')->__('Edit Customer Attribute')
            : Mage::helper('evogue_customer')->__('New Customer Attribute');

        $this->_initAction()
            ->_addBreadcrumb($label, $label)
            ->renderLayout();
    }

    public function validateAction() {
        $response = new Varien_Object();
        $response->setError(false);
        $attributeId        = $this->getRequest()->getParam('attribute_id');
        if (!$attributeId) {
            $attributeCode      = $this->getRequest()->getParam('attribute_code');
            $attributeObject    = $this->_initAttribute()
                ->loadByCode($this->_getEntityType()->getId(), $attributeCode);
            if ($attributeObject->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('evogue_customer')->__('Attribute with the same code already exists')
                );

                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction() {
        $data = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost() && $data) {
            $attributeObject = $this->_initAttribute();
            $helper = Mage::helper('evogue_customer');

            $attributeId = $this->getRequest()->getParam('attribute_id');
            if ($attributeId) {
                $attributeObject->load($attributeId);
                if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('evogue_customer')->__('You cannot edit this attribute.')
                    );
                    $this->_getSession()->addAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code']     = $attributeObject->getAttributeCode();
                $data['is_user_defined']    = $attributeObject->getIsUserDefined();
                $data['frontend_input']     = $attributeObject->getFrontendInput();
                $data['is_user_defined']    = $attributeObject->getIsUserDefined();
                $data['is_system']          = $attributeObject->getIsSystem();
            } else {
                $data['backend_model']      = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
                $data['source_model']       = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_type']       = $helper->getAttributeBackendTypeByInputType($data['frontend_input']);
                $data['is_user_defined']    = 1;
                $data['is_system']          = 0;

                $data['attribute_set_id']   = $this->_getEntityType()->getDefaultAttributeSetId();
                $data['attribute_group_id'] = Mage::getModel('eav/entity_attribute_set')
                    ->getDefaultGroupId($data['attribute_set_id']);
            }

            if (isset($data['used_in_forms'])) {
                $data['used_in_forms'][] = 'adminhtml_customer';
            }

            $defaultValueField = $helper->getAttributeDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $scopeKeyPrefix = ($this->getRequest()->getParam('website') ? 'scope_' : '');
                $data[$scopeKeyPrefix . 'default_value'] = $this->getRequest()->getParam($scopeKeyPrefix . $defaultValueField);
            }

            $data['entity_type_id']     = $this->_getEntityType()->getId();
            $data['validate_rules']     = $helper->getAttributeValidateRules($data['frontend_input'], $data);

            $attributeObject->addData($data);

            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $key) {
                    $attributeObject->setData('scope_' . $key, null);
                }
            }

            try {
                $attributeObject->save();
                Mage::dispatchEvent('evogue_customer_attribute_save', array(
                    'attribute' => $attributeObject
                ));

                $this->_getSession()->addSuccess(
                    Mage::helper('evogue_customer')->__('The customer attribute has been saved.')
                );
                $this->_getSession()->setAttributeData(false);
                if ($this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array(
                        'attribute_id'  => $attributeObject->getId(),
                        '_current'      => true
                    ));
                } else {
                    $this->_redirect('*/*/');
                }
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('evogue_customer')->__('An error occurred while saving the customer attribute.')
                );
                $this->_getSession()->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
        return;
    }

    public function deleteAction() {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            $attributeObject = $this->_initAttribute()->load($attributeId);
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()
                || !$attributeObject->getIsUserDefined())
            {
                $this->_getSession()->addError(
                    Mage::helper('evogue_customer')->__('You cannot delete this attribute.')
                );
                $this->_redirect('*/*/');
                return;
            }
            try {
                $attributeObject->delete();
                Mage::dispatchEvent('evogue_customer_attribute_delete', array(
                    'attribute' => $attributeObject
                ));

                $this->_getSession()->addSuccess(
                    Mage::helper('evogue_customer')->__('The customer attribute has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $attributeId, '_current' => true));
                return;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('evogue_customer')->__('An error occurred while deleting the customer attribute.')
                );
                $this->_redirect('*/*/edit', array('attribute_id' => $attributeId, '_current' => true));
                return;
            }
        }

        $this->_redirect('*/*/');
        return;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/attributes/customer_attributes');
    }
}
