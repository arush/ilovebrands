<?php
class Evogue_TargetRule_Model_Actions_Condition_Product_Special
    extends Mage_CatalogRule_Model_Rule_Condition_Product {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_targetrule/actions_condition_product_special');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions() {
        $conditions = array(
            array(
                'value' => 'evogue_targetrule/actions_condition_product_special_price',
                'label' => Mage::helper('evogue_targetrule')->__('Price (percentage)')
            )
        );

        return array(
            'value' => $conditions,
            'label' => Mage::helper('evogue_targetrule')->__('Product Special')
        );
    }
}
