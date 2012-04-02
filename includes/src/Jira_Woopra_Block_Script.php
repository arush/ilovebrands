<?php
/**
 * Woopra plugin for Magento 
 *
 * @category    design_default
 * @package     Jira_Woopra
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (c) 2010 Yireo (http://www.yireo.com/)
 * @license     Open Software License
 */

class Jira_Woopra_Block_Script extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();

        // Decide whether the Woopra Plugin has been actived
        $website_id = Mage::helper('woopra')->getApiKey();
        if(Mage::helper('woopra')->enabled() == true && !empty($website_id)) {
            $this->setTemplate('woopra/script.phtml');
        }
    }

    /*
     * Helper method to get data to show in Woopra
     *
     * @access public
     * @param string $key
     * @return string
     */
    public function getSetting($key = null)
    {
        static $data;
        if(empty($data)) {

            // Add generic settings
            $data = array(
                'website_id' => Mage::helper('woopra')->getApiKey(), // Woopra API key
                'enabled' => Mage::helper('woopra')->enabled(), // Plugin status
                'test' => Mage::helper('woopra')->test(), // Plugin testing mode
            );

            // Add things from the cart
            $data['cart_items'] = Mage::helper('checkout/cart')->getCart()->getItemsCount();
            $data['cart_total'] = Mage::getSingleton('checkout/session')->getQuote()->getBaseSubtotal();

            // Add customer records
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if(!empty($customer)) {

                $data['name'] = $customer->getName();
                $data['email'] = $customer->getEmail();
                $data['avatar'] = $customer->getAvatar();

                // Load the address
                $address = $customer->getDefaultBillingAddress();
                if(!empty($address)) {
                    $address = $customer->getDefaultShippingAddress();
                }

                // Add address data
                if(!empty($address)) {
                    $data['company'] = $address->getCompany();
                    $data['phone'] = $address->getTelephone();
                    $data['city'] = $address->getCity() . ' (' . $address->getCountryId() . ')';
                }
            }

            $currentCategory = Mage::registry('current_category');
            if(!empty($currentCategory)) {
                $data['category'] = $currentCategory->getName();
            }

            $currentProduct = Mage::registry('current_product');
            if(!empty($currentProduct)) {
                $data['product_sku'] = $currentProduct->getSku();
                $data['product_price'] = strip_tags(Mage::app()->getStore()->formatPrice($currentProduct->getPrice()));
            }
        }

        if(isset($data[$key])) {
            return $data[$key];
        } else {
            return null;
        }
    }
}
