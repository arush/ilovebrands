<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container {
    protected $_objectId = 'id';
    protected $_blockGroup = 'evogue_catalogevent';
    protected $_controller = 'adminhtml_event';

    protected function _prepareLayout() {
        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        } else {
            $this->_addButton(
                'save_and_continue',
                array(
                    'label' => $this->helper('evogue_catalogevent')->__('Save and Continue Edit'),
                    'class' => 'save',
                    'onclick'   => 'saveAndContinue()',
                ),
                1
            );

            $this->_formScripts[] = '
                function saveAndContinue() {
                    if (editForm.validator.validate()) {
                        $(editForm.formId).insert({bottom:
                            \'<\' + \'input type="hidden" name="_continue" value="1" /\' + \'>\'
                        });
                        editForm.submit();
                    }
                }
            ';
        }

        parent::_prepareLayout();

        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_category'));
        }

        if ($this->getRequest()->getParam('category')) {
            $this->_updateButton('back', 'label', $this->helper('evogue_catalogevent')->__('Back to Category'));
        }

        if ($this->getEvent()->isReadonly() && $this->getEvent()->getImageReadonly()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            $this->_removeButton('save_and_continue');
        }

        if (!$this->getEvent()->isDeleteable()) {
            $this->_removeButton('delete');
        }

        return $this;
    }


    public function getBackUrl() {
        if ($this->getRequest()->getParam('category')) {
            return $this->getUrl('*/catalog_category/edit',
                                array('clear' => 1, 'id' => $this->getEvent()->getCategoryId()));
        } elseif (!$this->getEvent()->getId() && $this->getEvent()->getCategoryId()) {
            return $this->getUrl('*/*/new',
                                 array('_current' => true, 'category_id' => null));
        }

        return parent::getBackUrl();
    }


    public function getHeaderText() {
        if ($this->getEvent()->getId()) {
            return Mage::helper('evogue_catalogevent')->__('Edit Catalog Event');
        }
        else {
            return Mage::helper('evogue_catalogevent')->__('Add Catalog Event');
        }
    }

    public function getEvent() {
        return Mage::registry('evogue_catalogevent_event');
    }

}
