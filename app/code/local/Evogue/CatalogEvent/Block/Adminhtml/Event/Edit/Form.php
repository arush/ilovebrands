<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function getActionUrl() {
        return $this->getUrl('*/*/save', array('_current' => true));
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('evogue_catalogevent/adminhtml_form_renderer_fieldset_element')
        );
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getActionUrl(),
                'method' => 'post',
                'field_name_suffix' => 'catalogevent',
                'enctype'=> 'multipart/form-data'
            )
        );

        $form->setHtmlIdPrefix('event_edit_');

        $fieldset = $form->addFieldset('general_fieldset',
            array(
                'legend' => Mage::helper('evogue_catalogevent')->__('Catalog Event Information'),
                'class'  => 'fieldset-wide'
            )
        );

        $this->_addElementTypes($fieldset);

        $currentCategory = Mage::getModel('catalog/category')
            ->load($this->getEvent()->getCategoryId());

        $fieldset->addField('category_name', 'note',
            array(
                'id'    => 'category_span',
                'label' => Mage::helper('evogue_catalogevent')->__('Category')
            )
        );

        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );

        $fieldset->addField('date_start', 'date', array(
                'label'        => Mage::helper('evogue_catalogevent')->__('Start Date'),
                'name'         => 'date_start',
                'required'     => true, 'time' => true,
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'format'       => $dateFormatIso
            ));

        $fieldset->addField('date_end', 'date', array(
                'label'        => Mage::helper('evogue_catalogevent')->__('End Date'),
                'name'         => 'date_end', 'required' => true,
                'time'         => true,
                'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                'format'       => $dateFormatIso
            ));

        $fieldset->addField('image', 'image', array(
                'label' => Mage::helper('evogue_catalogevent')->__('Image'),
                'scope' => 'store',
                'name'  => 'image'
             )
        );

        $fieldset->addField('sort_order', 'text', array(
                'label' => Mage::helper('evogue_catalogevent')->__('Sort Order'),
                'name'  => 'sort_order',
                'class' => 'validate-num qty'
             )
        );

        $statuses = array(
            Evogue_CatalogEvent_Model_Event::STATUS_UPCOMING => Mage::helper('evogue_catalogevent')->__('Upcoming'),
            Evogue_CatalogEvent_Model_Event::STATUS_OPEN => Mage::helper('evogue_catalogevent')->__('Open'),
            Evogue_CatalogEvent_Model_Event::STATUS_CLOSED => Mage::helper('evogue_catalogevent')->__('Closed')
        );

        $fieldset->addField('display_state_array', 'checkboxes', array(
                'label'  => Mage::helper('evogue_catalogevent')->__('Display Countdown Ticker On'),
                'name'   => 'display_state[]',
                'values' => array(
                    Evogue_CatalogEvent_Model_Event::DISPLAY_CATEGORY_PAGE => Mage::helper('evogue_catalogevent')->__('Category Page'),
                    Evogue_CatalogEvent_Model_Event::DISPLAY_PRODUCT_PAGE => Mage::helper('evogue_catalogevent')->__('Product Page')
                )
            ));

        if ($this->getEvent()->getId()) {
            $fieldset->addField('status', 'note', array(
                    'label' => Mage::helper('evogue_catalogevent')->__('Status'),
                    'text'  => ($this->getEvent()->getStatus() ? $statuses[$this->getEvent()->getStatus()] : $statuses[Evogue_CatalogEvent_Model_Event::STATUS_UPCOMING])
            ));
        }

        $form->setValues($this->getEvent()->getData());

        if ($currentCategory && $this->getEvent()->getId()) {
            $form->getElement('category_name')->setText(
                '<a href="' . Mage::helper('adminhtml')->getUrl('adminhtml/catalog_category/edit',
                                                            array('clear' => 1, 'id' => $currentCategory->getId()))
                . '">' . $currentCategory->getName() . '</a>'
            );
        } else {
            $form->getElement('category_name')->setText(
                '<a href="' . $this->getParentBlock()->getBackUrl()
                . '">' . $currentCategory->getName() . '</a>'
            );
        }

        $form->getElement('date_start')->setValue($this->getEvent()->getStoreDateStart());
        $form->getElement('date_end')->setValue($this->getEvent()->getStoreDateEnd());

        if ($this->getEvent()->getDisplayState()) {
            $form->getElement('display_state_array')->setChecked($this->getEvent()->getDisplayState());
        }

        $form->setUseContainer(true);
        $form->setDataObject($this->getEvent());
        $this->setForm($form);

        if ($this->getEvent()->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                if ($element->getId() !== 'image') {
                    $element->setReadonly(true, true);
                }
            }
        }

        if ($this->getEvent()->getImageReadonly()) {
            $form->getElement('image')->setReadonly(true, true);
        }
        return parent::_prepareForm();
    }

    public function getEvent() {
        return Mage::registry('evogue_catalogevent_event');
    }

    protected function _getAdditionalElementTypes() {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('evogue_catalogevent/adminhtml_event_helper_image')
        );
    }

}
