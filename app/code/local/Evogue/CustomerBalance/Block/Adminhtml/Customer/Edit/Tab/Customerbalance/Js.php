<?php
class Evogue_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Js extends Mage_Adminhtml_Block_Template {

    public function getCustomerWebsite() {
        return Mage::registry('current_customer')->getWebsiteId();
    }

    public function getWebsitesJson() {
        $result = array();
        foreach (Mage::app()->getWebsites() as $websiteId => $website) {
            $result[$websiteId] = array(
                'name'          => $website->getName(),
                'website_id'    => $websiteId,
                'currency_code' => $website->getBaseCurrencyCode(),
                'groups'        => array()
            );

            foreach ($website->getGroups() as $groupId => $group) {
                $result[$websiteId]['groups'][$groupId] = array(
                    'name' => $group->getName()
                );

                foreach ($group->getStores() as $storeId => $store) {
                    $result[$websiteId]['groups'][$groupId]['stores'][] = array(
                        'name'     => $store->getName(),
                        'store_id' => $storeId,
                    );
                }
            }
        }

        return Mage::helper('core')->jsonEncode($result);
    }
}
