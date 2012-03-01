<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Segment extends Mage_Rule_Model_Condition_Abstract {

    protected $_inputType = 'multiselect';


    public function getDefaultOperatorInputByType() {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'multiselect' => array('==', '!=', '()', '!()'),
            );
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getValueAfterElementHtml() {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="'
            . Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif')
            . '" alt="" class="v-middle rule-chooser-trigger" title="'
            . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
    }

    public function getValueElementType() {
        return 'text';
    }

    public function getValueElementChooserUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/customersegment/chooserGrid', array(
            'value_element_id' => $this->_valueElement->getId(),
            'form' => $this->getJsFormObject(),
        ));
    }

    public function getExplicitApply() {
        return true;
    }

    public function asHtml() {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('evogue_customersegment')->__('If Customer Segment %s %s',
                $this->getOperatorElementHtml(), $this->_valueElement->getHtml())
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }

    public function loadOperatorOptions() {
        parent::loadOperatorOptions();
        $this->setOperatorOption(array(
            '=='  => Mage::helper('evogue_customersegment')->__('matches'),
            '!='  => Mage::helper('evogue_customersegment')->__('does not match'),
            '()'  => Mage::helper('evogue_customersegment')->__('is one of'),
            '!()' => Mage::helper('evogue_customersegment')->__('is not one of'),
        ));
        return $this;
    }

    public function getValueParsed() {
        $value = $this->getData('value');
        $value = array_map('trim', explode(',',$value));
        return $value;
    }

    public function validate(Varien_Object $object) {
        $customer = null;
        if ($object->getQuote()) {
            $customer = $object->getQuote()->getCustomer();
        }
        if (!$customer) {
            return false;
        }

        $segments = Mage::getSingleton('evogue_customersegment/customer')->getCustomerSegmentIdsForWebsite(
            $customer->getId(),
            $object->getQuote()->getStore()->getWebsite()->getId()
        );
        return $this->validateAttribute($segments);
    }
}
