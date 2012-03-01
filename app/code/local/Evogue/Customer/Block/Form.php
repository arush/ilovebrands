<?php
class Evogue_Customer_Block_Form extends Mage_Core_Block_Template {

    protected $_renderBlockTypes    = array();

    protected $_renderBlocks        = array();

    protected $_formCode;

    protected $_entityModelClass;

    protected $_entityType;

    protected $_form;

    protected $_entity;

    protected $_fieldIdFormat   = '%1$s';

    protected $_fieldNameFormat = '%1$s';

    public function addRenderer($type, $block, $template) {
        $this->_renderBlockTypes[$type] = array(
            'block'     => $block,
            'template'  => $template,
        );

        return $this;
    }

    protected function _prepareLayout() {
        $template = $this->getLayout()->getBlock('customer_form_template');
        if ($template) {
            foreach ($template->getRenderers() as $type => $data) {
                $this->addRenderer($type, $data['block'], $data['template']);
            }
        }
        return parent::_prepareLayout();
    }

    public function getRenderer($type) {
        if (!isset($this->_renderBlocks[$type])) {
            if (isset($this->_renderBlockTypes[$type])) {
                $data   = $this->_renderBlockTypes[$type];
                $block  = $this->getLayout()->createBlock($data['block']);
                if ($block) {
                    $block->setTemplate($data['template']);
                }
            } else {
                $block = false;
            }
            $this->_renderBlocks[$type] = $block;
        }
        return $this->_renderBlocks[$type];
    }

    public function setEntity(Mage_Core_Model_Abstract $entity) {
        $this->_entity = $entity;
        return $this;
    }

    public function setEntityModelClass($model) {
        $this->_entityModelClass = $model;
        return $this;
    }

    public function setEntityType($entityType) {
        $this->_entityType = Mage::getSingleton('eav/config')->getEntityType($entityType);
        return $this;
    }

    public function getEntity() {
        if (is_null($this->_entity)) {
            if ($this->_entityModelClass) {
                $this->_entity = Mage::getModel($this->_entityModelClass);
            }
        }
        return $this->_entity;
    }

    public function setForm(Mage_Customer_Model_Form $form) {
        $this->_form = $form;
        return $this;
    }

    public function setFormCode($code) {
        $this->_formCode = $code;
        return $this;
    }

    public function getForm() {
        if (is_null($this->_form)) {
            $this->_form = Mage::getModel('customer/form')
                ->setFormCode($this->_formCode)
                ->setEntity($this->getEntity());
            if ($this->_entityType) {
                $this->_form->setEntityType($this->_entityType);
            }
            $this->_form->initDefaultValues();
        }
        return $this->_form;
    }

    public function hasUserDefinedAttributes() {
        return count($this->getUserDefinedAttributes()) > 0;
    }

    public function getUserDefinedAttributes() {
        $attributes = array();
        foreach ($this->getForm()->getUserAttributes() as $attribute) {
            if ($this->getExcludeFileAttributes() && in_array($attribute->getFrontendInput(), array('image', 'file'))) {
                continue;
            }
            if ($attribute->getIsVisible()) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $attributes;
    }

    public function getAttributeHtml(Mage_Customer_Model_Attribute $attribute) {

        $type   = $attribute->getFrontendInput();
        $block  = $this->getRenderer($type);
        
        if ($block) {
            $block->setAttributeObject($attribute)
                ->setEntity($this->getEntity())
                ->setFieldIdFormat($this->_fieldIdFormat)
                ->setFieldNameFormat($this->_fieldNameFormat);
            return $block->toHtml();
        }
        return false;
    }

    public function setFieldIdFormat($format) {
        $this->_fieldIdFormat = $format;
        return $this;
    }

    public function setFieldNameFormat($format) {
        $this->_fieldNameFormat = $format;
        return $this;
    }

    public function isShowContainer()
    {
        if ($this->hasData('show_container')) {
            return $this->getData('show_container');
        }
        return true;
    }
}
