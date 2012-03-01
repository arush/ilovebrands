<?php
class Evogue_CustomerBalance_Block_Adminhtml_Sales_Order_Creditmemo_Controls
 extends Mage_Core_Block_Template {
    public function canRefundToCustomerBalance() {
        if (Mage::registry('current_creditmemo')->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    public function canRefundMoneyToCustomerBalance() {
        if (!Mage::registry('current_creditmemo')->getGrandTotal()) {
            return false;
        }

        if (Mage::registry('current_creditmemo')->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    public function getReturnValue() {
        $max = Mage::registry('current_creditmemo')->getCustomerBalanceReturnMax();
        if ($max) {
            return $max;
        }
        return 0;
    }
}
