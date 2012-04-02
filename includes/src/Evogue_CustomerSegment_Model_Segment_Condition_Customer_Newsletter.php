<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Customer_Newsletter
    extends Evogue_CustomerSegment_Model_Condition_Abstract {
    protected $_inputType = 'select';

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_customer_newsletter');
        $this->setValue(1);
    }

    public function getMatchedEvents() {
        return array('customer_save_commit_after', 'newsletter_subscriber_save_commit_after');
    }

    public function getNewChildSelectOptions() {
        return array(array('value' => $this->getType(),
            'label'=>Mage::helper('evogue_customersegment')->__('Newsletter Subscription')));
    }

    public function asHtml() {
        $operator = $this->getOperatorElementHtml();
        $element = $this->getValueElementHtml();
        return $this->getTypeElementHtml()
            .Mage::helper('evogue_customersegment')->__('Customer is %s to newsletter.', $element)
            .$this->getRemoveLinkHtml();
    }

    public function getValueElementType() {
        return 'select';
    }

    public function loadValueOptions() {
        $this->setValueOption(array(
            '1'  => Mage::helper('evogue_customersegment')->__('subscribed'),
            '0' => Mage::helper('evogue_customersegment')->__('not subscribed'),
        ));
        return $this;
    }

    public function getConditionsSql($customer, $website) {
        $table = $this->getResource()->getTable('newsletter/subscriber');
        $value = $this->getValue();

        $select = $this->getResource()->createSelect()
            ->from(array('main' => $table), array(new Zend_Db_Expr($value)))
            ->where($this->_createCustomerFilter($customer, 'main.customer_id'))
            ->where('main.subscriber_status = ?', Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
            ->limit(1);
        $this->_limitByStoreWebsite($select, $website, 'main.store_id');
        if (!$value) {
            $select = 'IFNULL(('.$select.'), 1)';
        }
        return $select;
    }
}
