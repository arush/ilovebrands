<?php
class Evogue_TargetRule_Model_Rule_Condition_Product_Attributes
    extends Mage_CatalogRule_Model_Rule_Condition_Product {

    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    protected $_disabledTargetRuleCodes = array(
        'status' // products with status 'disabled' cannot be shown as related/cross-sells/up-sells thus rule code is useless
    );

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/rule_condition_product_attributes');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            if (! in_array($code, $this->_disabledTargetRuleCodes)) {
                $conditions[] = array(
                    'value' => $this->getType() . '|' . $code,
                    'label' => $label
                );
            }
        }

        return array(
            'value' => $conditions,
            'label' => Mage::helper('evogue_targetrule')->__('Product Attributes')
        );
    }
}
