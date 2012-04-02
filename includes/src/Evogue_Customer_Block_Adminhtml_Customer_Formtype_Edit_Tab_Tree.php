<?php
class Evogue_Customer_Block_Adminhtml_Customer_Formtype_Edit_Tab_Tree
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _getFormType() {
        return Mage::registry('current_form_type');
    }

    public function getTreeButtonsHtml() {
        $addButtonData = array(
            'id'        => 'add_node_button',
            'label'     => Mage::helper('evogue_customer')->__('New Fieldset'),
            'onclick'   => 'formType.newFieldset()',
            'class'     => 'add',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData($addButtonData)->toHtml();
    }

    public function getFieldsetButtonsHtml() {
        $buttons = array();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'save_node_button',
            'label'     => Mage::helper('evogue_customer')->__('Save'),
            'onclick'   => 'formType.saveFieldset()',
            'class'     => 'save',
        ))->toHtml();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'delete_node_button',
            'label'     => Mage::helper('evogue_customer')->__('Remove'),
            'onclick'   => 'formType.deleteFieldset()',
            'class'     => 'delete',
        ))->toHtml();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'cancel_node_button',
            'label'     => Mage::helper('evogue_customer')->__('Cancel'),
            'onclick'   => 'formType.cancelFieldset()',
            'class'     => 'cancel',
        ))->toHtml();

        return join(' ', $buttons);
    }

    public function getStores() {
        if (!$this->hasData('stores')) {
            $this->setData('stores', Mage::app()->getStores(false));
        }
        return $this->_getData('stores');
    }

    public function getStoresJson() {
        $result = array();
        $stores = $this->getStores();
        foreach ($stores as $stores) {
            $result[$stores->getId()] = $stores->getName();
        }

        return Mage::helper('core')->jsonEncode($result);
    }

    public function getAttributesJson() {
        $nodes = array();

        $fieldsetCollection = Mage::getModel('eav/form_fieldset')->getCollection()
            ->addTypeFilter($this->_getFormType())
            ->setSortOrder();
        $elementCollection = Mage::getModel('eav/form_element')->getCollection()
            ->addTypeFilter($this->_getFormType())
            ->setSortOrder();
        foreach ($fieldsetCollection as $fieldset) {
            $node = array(
                'node_id'   => $fieldset->getId(),
                'parent'    => null,
                'type'      => 'fieldset',
                'code'      => $fieldset->getCode(),
                'label'     => $fieldset->getLabel()
            );

            foreach ($fieldset->getLabels() as $storeId => $label) {
                $node['label_' . $storeId] = $label;
            }

            $nodes[] = $node;
        }

        foreach ($elementCollection as $element) {
            $nodes[] = array(
                'node_id'   => 'a_' . $element->getId(),
                'parent'    => $element->getFieldsetId(),
                'type'      => 'element',
                'code'      => $element->getAttribute()->getAttributeCode(),
                'label'     => $element->getAttribute()->getFrontend()->getLabel()
            );
        }

        return Mage::helper('core')->jsonEncode($nodes);
    }

    public function getTabLabel() {
        return Mage::helper('evogue_customer')->__('Attributes');
    }

    public function getTabTitle() {
        return Mage::helper('evogue_customer')->__('Attributes');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
}
