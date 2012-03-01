<?php

class Evogue_CustomerBalance_Block_Account_Balance extends Mage_Core_Block_Template {

    public function getBalance() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return 0;
        }

        $model = Mage::getModel('evogue_customerbalance/balance')
            ->setCustomerId($customerId)
            ->loadByCustomer();

        return $model->getAmount();
    }
}
