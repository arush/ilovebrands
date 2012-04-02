<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * Helper for things that need to be done from POS-level apps (webapp, API, etc.)
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Helper_Pos extends Mage_Core_Helper_Abstract
{
    const INSTORE_PRODUCT_KEY = 'rewardsinstore/dev/instore_product_id';
    
    public function rewardCustomer($customerId, $storefrontId, $data, $source = 'webapp')
    {
        // TODO: Add integration with Magento orders later
        //$createInstoreOrder = Mage::getStoreConfig('rewardsinstore/general/create_instore_orders');
        $createInstoreOrder = false;
        
        if ($createInstoreOrder) {
            
            $orderId = $this->createOrder($customerId, $storefrontId, $data);
            // TODO: refactor this into order->getAssociatedTransfers();
            $transfers = Mage::getModel('rewards/transfer')->getTransfersAssociatedWithOrder($orderId);
        } else {
            
            $quote = $this->createInstoreQuote($customerId, $storefrontId, $data);
            
            Mage::dispatchEvent('rewardsinstore_order_placed_before', array(
                'quote'  => $quote,
                'source' => $source
            ));
            
            $quote->placeInstoreQuote();
            
            Mage::dispatchEvent('rewardsinstore_order_placed_after', array(
                'quote'  => $quote,
                'source' => $source
            ));
            
            $transfers = $quote->getAssociatedTransfers();
        }
        
        return $transfers;
    }
    
    public function createOrder($customerId, $storefrontId, $data)
    {
        $quote = $this->createInstoreQuote($customerId, $storefrontId, $data);
        
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();
        
        // TODO - we might want to change some lower level logic so Instore orders start off with this status
        $transfers = Mage::getModel('rewards/transfer')->getTransfersAssociatedWithOrder($order->getId());
        foreach ($transfers as $transfer) {
            $transfer->setStatus($transfer->getStatus(), TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)
                ->save();
        }
        
        return $order->getId();
    }
    
    public function createInstoreQuote($customerId, $storefrontId, $data)
    {
        // Product used in all Instore quotes and orders
        $product = $this->getInstoreProduct();
        
        // Define our current instore attributes
        $qty = 1;
        $subtotal = 0;
        
        // Process data fields
        if (!is_null($data)) {
            $qty = array_key_exists('qty', $data) ? $data['qty'] : $qty;
            $subtotal = array_key_exists('subtotal', $data) && $qty > 0 ? $data['subtotal']/$qty : $subtotal;
        }
        
        $quote = Mage::getModel('rewardsinstore/quote')
            ->setStorefrontId($storefrontId);
        
        $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
            
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($storefront->getWebsiteId())
            ->load($customerId);
        $quote->assignCustomer($customer);
        
        $buyInfo = array(
            'qty' => $qty,
        );
        
        $item = $quote->addProduct($product, new Varien_Object($buyInfo));
        $item->setOriginalCustomPrice($subtotal);
        $item->setCustomPrice($subtotal);
        
        // TODO: Put the storefront address here
        $addressData = array(
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'street' => $storefront->getStreet(),
            'city' => $storefront->getCity(),
            'postcode' => $storefront->getPostcode(),
            'telephone' => $storefront->getTelephone(),
            'country_id' => $storefront->getCountryId(),
            'region_id' => $storefront->getRegionId()
        );
        
        $billingAddress = $quote->getBillingAddress()->addData($addressData);
        
        $quote->getPayment()->importData(array('method' => 'rewardsinstore'));
        
        $quote->collectTotals()->save();
        
        return $quote;
    }
    
    /**
     * TODO description
     * 
     * @param $customer
     */
    public function emailCustomer($customer)
    {
        if ($customer->getIsCreatedInstore() && !$customer->getIsWelcomeEmailSent()) {
            $storeId = $customer->getStoreId();
            if ($points = $customer->getUsablePointsBalance(1)) {
                $emailTemplate = Mage::getStoreConfig('rewardsinstore/customer_signup/email_template', $storeId);
                $emailIdentity = 'general';
            } else {
                $emailTemplate = Mage::getStoreConfig('customer/create_account/email_template', $storeId);
                $emailIdentity = Mage::getStoreConfig('customer/create_account/email_identity', $storeId);
            }
            
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area'=>'frontend', 'store'=> $storeId))
                ->sendTransactional(
                    $emailTemplate,
                    $emailIdentity,
                    $customer->getEmail(),
                    Mage::helper('rewardsinstore/customer')->getCustomerName($customer),
                    array('customer' => Mage::helper('rewardsinstore/customer')->getMagentoCustomer($customer), 'back_url' => "", 'points' => $points));
            $translate->setTranslateInline(true);
            
            $customer->setIsWelcomeEmailSent(true)
                ->save();
        }
    }
    
    public function log($msg)
    {
        Mage::helper('rewardsinstore')->log($msg);
    }
    
    /**
     * Creates Instore product if doesn't exist,
     * otherwise returns instance of existing instore product.
     * Typically the instore product is only created once.
     *
     * @return Mage_Catalog_Model_Product Instore product
     */
    public function getInstoreProduct()
    {
        // Check that Insore product_id has been previously saved
        $instore_product_id = $this->getInstoreProductConfig();
        
        // Create new Instore product for the first time
        if (is_null($instore_product_id)) {
            $instore_product_id = $this->createInstoreProduct();
        }
        
        $product = Mage::getModel('catalog/product')->load($instore_product_id);
        
        // Return product if exists, otherwise recreate it (may have been deleted by admin)
        if ($product->getId()) {
            $this->setInstoreProductConfig($product->getId());
            return $product;
        } else {
            $instore_product_id = $this->createInstoreProduct();
            $product = Mage::getModel('catalog/product')->load($instore_product_id);
        }
        
        // Save product Id for future reference
        if ($product->getId()) {
            $this->setInstoreProductConfig($product->getId());
        } else {
            throw new Exception('Error creating Instore product.');
        }
        
        return $product;
    }
    
    /**
     * Creates a virtual product which is used in Instore quotes and orders.
     * The id of this product is stored in system config for future reference.
     * 
     * @param boolean $force creates new instore product even if one alrady exists
     * @return int productId
     */
    public function createInstoreProduct()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $product = Mage::getModel('catalog/product'); 
        
        // Build our virtual instore product 
        $product->setSku('rewardsinstore_product_' . rand()); 
        $product->setAttributeSetId($product->getDefaultAttributeSetId());
        $product->setTypeId('virtual'); 
        $product->setName('Sweet Tooth Instore Product'); 
        $product->setStatus(1); 
        $product->setTaxClassId(0); // default tax class 
        $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE); 
        $product->setPrice(0);
        $product->setDescription('This product is used by Sweet Tooth Instore. Ok to delete, but it will be recreated on next Instore order'); 
        $product->setShortDescription('Sweet Tooth Instore Product'); 
        
        // TODO: add observer for website creation to add this product to each new website
        $websites_ids = Mage::getModel('core/website')->getCollection()->getAllIds();
        // Remove the default website in this set
        $valid_websites_ids = array_diff($websites_ids, array(0));
        $product->setWebsiteIDs($valid_websites_ids);
        
        $product->setStockData(array( 
            'use_config_manage_stock' => 0, 
            'manage_stock' => 0 
        ));
        
        try {
            $product->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
        
        return $product->getId();
    }
    
    protected function setInstoreProductConfig($product_id)
    {
        Mage::getConfig()
            ->saveConfig(self::INSTORE_PRODUCT_KEY, $product_id)
            ->cleanCache();
    }
    
    public function getInstoreProductConfig()
    {
        return Mage::getStoreConfig(self::INSTORE_PRODUCT_KEY);
    }
}
