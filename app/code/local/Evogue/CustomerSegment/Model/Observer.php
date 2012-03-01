<?php
class Evogue_CustomerSegment_Model_Observer {

    public function addSegmentsToSalesRuleCombine(Varien_Event_Observer $observer) {
        if (!Mage::helper('evogue_customersegment')->isEnabled()) {
            return;
        }
        $additional = $observer->getEvent()->getAdditional();
        $additional->setConditions(array(array(
            'label' => Mage::helper('evogue_customersegment')->__('Customer Segment'),
            'value' => 'evogue_customersegment/segment_condition_segment'
        )));
    }

    public function processCustomerEvent(Varien_Event_Observer $observer) {
        $eventName = $observer->getEvent()->getName();
        $customer  = $observer->getEvent()->getCustomer();
        $dataObject= $observer->getEvent()->getDataObject();
        $customerId= false;

        if ($customer) {
            $customerId = $customer->getId();
        }
        if (!$customerId && $dataObject) {
            $customerId = $dataObject->getCustomerId();
        }

        if ($customerId) {
            Mage::getSingleton('evogue_customersegment/customer')->processCustomerEvent(
                $eventName,
                $customerId
            );
        }
    }

    public function processEvent(Varien_Event_Observer $observer) {
        $eventName = $observer->getEvent()->getName();
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            return $this;
        }
        $customer = $customerSession->getCustomer();
        $website = Mage::app()->getStore()->getWebsite();
        Mage::getSingleton('evogue_customersegment/customer')->processEvent($eventName, $customer, $website);
    }

    public function processQuote(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        $customer = $quote->getCustomer();
        if ($customer && $customer->getId()) {
            $website = $quote->getStore()->getWebsite();
            Mage::getSingleton('evogue_customersegment/customer')->processCustomer($customer, $website);
        }
    }

    public function evogueCustomerAttributeEditPrepareForm(Varien_Event_Observer $observer) {
        $form       = $observer->getEvent()->getForm();
        $fieldset   = $form->getElement('base_fieldset');
        $fieldset->addField('is_used_for_customer_segment', 'select', array(
            'name'      => 'is_used_for_customer_segment',
            'label'     => Mage::helper('evogue_customersegment')->__('Use in Customer Segment'),
            'title'     => Mage::helper('evogue_customersegment')->__('Use in Customer Segment'),
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
    }
}
