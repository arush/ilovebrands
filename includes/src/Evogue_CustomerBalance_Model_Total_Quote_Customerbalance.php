<?php
class Evogue_CustomerBalance_Model_Total_Quote_Customerbalance extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    public function __construct() {
        $this->setCode('customerbalance');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return $this;
        }
        $quote = $address->getQuote();
        if (!$quote->getCustomerBalanceCollected()) {
            $quote->setBaseCustomerBalanceAmountUsed(0);
            $quote->setCustomerBalanceAmountUsed(0);

            $quote->setCustomerBalanceCollected(true);
        }

        $baseTotalUsed = $totalUsed = $baseUsed = $used = 0;

        $baseBalance = $balance = 0;
        if ($quote->getCustomer()->getId()) {
            if ($quote->getUseCustomerBalance()) {
                $store = Mage::app()->getStore($quote->getStoreId());
                $baseBalance = Mage::getModel('evogue_customerbalance/balance')
                    ->setCustomer($quote->getCustomer())
                    ->setCustomerId($quote->getCustomer()->getId())
                    ->setWebsiteId($store->getWebsiteId())
                    ->loadByCustomer()
                    ->getAmount();
                $balance = $quote->getStore()->convertPrice($baseBalance);
            }
        }

        $baseAmountLeft = $baseBalance - $quote->getBaseCustomerBalanceAmountUsed();
        $amountLeft = $balance - $quote->getCustomerBalanceAmountUsed();

        if ($baseAmountLeft >= $address->getBaseGrandTotal()) {
            $baseUsed = $address->getBaseGrandTotal();
            $used = $address->getGrandTotal();

            $address->setBaseGrandTotal(0);
            $address->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseAmountLeft);
            $address->setGrandTotal($address->getGrandTotal()-$amountLeft);
        }

        $baseTotalUsed = $quote->getBaseCustomerBalanceAmountUsed() + $baseUsed;
        $totalUsed = $quote->getCustomerBalanceAmountUsed() + $used;

        $quote->setBaseCustomerBalanceAmountUsed($baseTotalUsed);
        $quote->setCustomerBalanceAmountUsed($totalUsed);

        $address->setBaseCustomerBalanceAmount($baseUsed);
        $address->setCustomerBalanceAmount($used);

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return $this;
        }
        if ($address->getCustomerBalanceAmount()) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('evogue_customerbalance')->__('Store Credit'),
                'value'=>-$address->getCustomerBalanceAmount(),
            ));
        }
        return $this;
    }
}
