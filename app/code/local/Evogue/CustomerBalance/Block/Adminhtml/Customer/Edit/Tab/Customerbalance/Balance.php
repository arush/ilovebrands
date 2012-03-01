<?php
class Evogue_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance extends Mage_Adminhtml_Block_Template {
    public function getOneBalanceTotal() {
        return 0;
    }

    public function shouldShowOneBalance() {
        return false;
    }

    public function getDeleteOrphanBalancesButton() {
        $customer = Mage::registry('current_customer');
        $balance = Mage::getModel('evogue_customerbalance/balance');
        if ($balance->getOrphanBalancesCount($customer->getId()) > 0) {
            return $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('evogue_customerbalance')->__('Delete Orphan Balances'),
                'onclick'   => 'setLocation(\'' . $this->getDeleteOrphanBalancesUrl() .'\')',
                'class'     => 'scalable delete',
            ))->toHtml();
        }
        return '';
    }

    public function getDeleteOrphanBalancesUrl() {
        return $this->getUrl('*/customerbalance/deleteOrphanBalances', array('_current' => true, 'tab' => 'customer_info_tabs_customerbalance'));
    }
}
