<?php
class Evogue_CustomerBalance_Block_Sales_Order_Customerbalance extends Mage_Core_Block_Template {
    public function getOrder() {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals() {
        if ((float)$this->getSource()->getCustomerBalanceAmount() == 0) {
            return $this;
        }
        $total = new Varien_Object(array(
            'code'      => $this->getNameInLayout(),
            'block_name'=> $this->getNameInLayout(),
            'area'      => $this->getArea()
        ));
        $after = $this->getAfterTotal();
        if (!$after) {
            $after = 'giftcards';
        }
        $this->getParentBlock()->addTotal($total, $after);
        return $this;
    }

    public function getLabelProperties() {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties() {
        return $this->getParentBlock()->getValueProperties();
    }
}
