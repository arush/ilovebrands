<?php
class Evogue_TargetRule_Model_Observer {
    public function prepareTargetRuleSave(Varien_Event_Observer $observer) {
        $_vars = array('targetrule_rule_based_positions', 'targetrule_position_behavior');
        $_varPrefix = array('related_', 'upsell_', 'crosssell_');
        if ($product = $observer->getEvent()->getProduct()) {
            foreach ($_vars as $var) {
                foreach ($_varPrefix as $pref) {
                    $v = $pref . $var;
                    if ($product->getData($v.'_default') == 1) {
                        $product->setData($v, null);
                    }
                }
            }
        }
    }

    public function catalogProductAfterSave(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();

        $indexResource = Mage::getResourceSingleton('evogue_targetrule/index');

        $indexResource->removeIndexByProductIds($product->getId());

        $indexResource->removeProductIndex($product->getId());

        $ruleCollection = Mage::getResourceModel('evogue_targetrule/rule_collection');
        foreach ($ruleCollection as $rule) {
            if ($rule->validate($product)) {
                $indexResource->saveProductIndex($rule->getId(), $product->getId());
            }
        }
    }
}
