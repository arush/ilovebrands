<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
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
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Model_Paypal_Standard extends Mage_Paypal_Model_Standard {

    //@nelkaake Sunday April 25, 2010 : Calculated in the base currency.
    public function getPaypalZeroCheckoutFee() {
        return Mage::helper('rewards/config_paypal')->getPaypalCheckoutFee();
    }

    /**
     * Appends Sweet Tooth information 
     * @note: As of Sweet Tooth 1.5.0.3, this rewrite has been replaced with the TBT_Rewards_Model_Paypal_Observer observer for 
     * all versions of Magento greater than or equal to 1.4.2.  Previous versions do not dispatch the paypal_prepare_line_items 
     * event
     * There are two major things we need to do here: 
     * 1. If the balance is 0, make sure there is at least 1 penny in the subtotal so PayPal lets us checkout.  
     * 2. Gather all of our catalog points redemption discounts and add them to the final cart discount total.
     * @deprecated for Magento 1.4.2+ as of  Sweet Tooth 1.5.0.3
     *
     * @return array paypal checkout fields
     */
    public function getStandardCheckoutFormFields() {
        
        $scf = parent::getStandardCheckoutFormFields();
        // Only run for Magento 1.4.0.x and lower; newer versions use thew paypal_prepare_line_items event obsrever in the TBT_Rewards_Model_Paypal_Observer class
        if ( Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.2') ) {
            return $scf;
        }
        
        $items = $this->_getQuote()->getAllItems();
        
        //@nelkaake -a 16/11/10: 
        

        Mage::getSingleton('rewards/redeem')->refactorRedemptions($items);
        
        // In Magento 1.4.1 - 1.4.1.1 for some reason discount_amount_cart is used instead of discount_amount
        if ( Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.1') ) {
            $disc_attr = 'discount_amount_cart';
        } else {
            $disc_attr = 'discount_amount';
        }
        
        // Init discount amount field
        if ( ! isset($scf[$disc_attr]) ) {
            $scf[$disc_attr] = 0;
        }
        $scf[$disc_attr] = (float) $scf[$disc_attr];
        
        //@nelkaake -a 16/11/10: Figure out the accumulated difference in price so we can add to the discount amount 
        

        $acc_diff = $this->getDiscountDisplacement();
        
        $scf[$disc_attr] += $acc_diff;
        
        //@nelkaake Added on Monday October 4, 2010: Uncomment this if you want to see what's being sent to PayPal standard checkout
        //Mage::helper('rewards/debug')->dd(array($scf, Mage::helper('rewards/debug')->getSimpleBacktrace() ));
        

        return $scf;
    }

    /**
     * We're discounting the whole amount, so we need to add a premium in order for PayPal to see the output.
     * This is because there is a bug in Magento 1.4+ that does not allow you to checkout if the total
     * is $0 but the tax amount is more than $0.       
     * @param float $discountAmount
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model           
     * @return float negative number to be added to the paypal total checkout discount amount.
     */
    protected function _getPaypalCheckoutFee($discountAmount, $_quote) {
        $discountAmount = (float) $discountAmount;
        $ppFee = 0;
        
        if ( $this->_doAddPaypalCheckoutFee($discountAmount, $_quote) ) {
            $ppFee = - ($this->getPaypalZeroCheckoutFee());
        }
        
        return $ppFee;
    }

    /**    
     * Decides whethero rn ot to add a paypal checkout fee (usually 0.01).
     * @param float $discountAmount
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return boolean true if you should add the paypal checkout fee to the discount amount.
     */
    protected function _doAddPaypalCheckoutFee($discountAmount, $_quote) {
        
        // if the discount amount is less than the subtotal, then never add the paypal fee
        if ( $discountAmount < $_quote->getSubtotal() ) {
            return false;
        }
        
        // Only add the fee if the tax amount is greater than zero for at least one item.
        foreach ($_quote->getAllItems() as $item) {
            if ( (float) $item->getTaxAmount() > 0 ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the total amount of discount displacement due to catalog redemption rules that needs to 
     * be subtracted from the grand total.
     * This is a positive amount          
     * 
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return float
     */
    public function getDiscountDisplacement($_quote = null) {
        
        if ( ! ($_quote instanceof Mage_Sales_Model_Order || $_quote instanceof Mage_Sales_Model_Quote) ) {
            $_quote = $this->_getQuote();
        }
        $items = $_quote->getAllItems();
        
        Mage::getSingleton('rewards/redeem')->refactorRedemptions($items);
        
        //@nelkaake -a 16/11/10: Figure out the accumulated difference in price so we can add to the discount amount 
        $acc_diff = 0;
        
        $acc_diff = $this->_getTotalCatalogDiscount($_quote);
        
        $acc_diff = $_quote->getStore()->roundPrice($acc_diff);
        if ( $acc_diff == - 0 ) $acc_diff = 0;
        
        $acc_diff = (float) $acc_diff + $this->_getPaypalCheckoutFee($acc_diff, $_quote);
        
        return $acc_diff;
    }

    /**
     * Returns a quote model that is applicable to this checkout model
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        
        if ( $this->getHasEnsuredQuote() ) return $this->getEnsuredQuote();
        
        $quote = $this->getQuote();
        $items = $quote->getAllItems();
        if ( empty($items) ) {
            $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            $quote_id = $order->getQuoteId();
            $quote = Mage::getModel('rewards/sales_quote')->load($order->getQuoteId());
        }
        $this->setHasEnsuredQuote(true);
        $this->setEnsuredQuote($quote);
        
        return $quote;
    }

    /**
     * Fetches the redemption calculator model
     *
     * @return TBT_Rewards_Model_Redeem
     */
    protected function _getRedeemer() {
        return Mage::getSingleton('rewards/redeem');
    }

    /**
     * Returns the total accumulated catalog discounts on the quote model that is in this class
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return int negative discount amount
     */
    protected function _getTotalCatalogDiscount($_quote = null) {
        
        if ( ! ($_quote instanceof Mage_Sales_Model_Order || $_quote instanceof Mage_Sales_Model_Quote) ) {
            $_quote = $this->_getQuote();
        }
        $items = $_quote->getAllItems();
        
        // If the rewards catalog discount is already stored in the quote, just use that.
        $quote_discount_amount = $_quote->getRewardsDiscountAmount();
        if ( $quote_discount_amount ) {
            return $quote_discount_amount;
        }
        
        if ( ! is_array($items) ) {
            $items = array(
                $items
            );
        }
        
        $acc_discount = 0;
        foreach ($items as $item) {
            $acc_discount += $this->_getTotalItemCatalogDiscount($item);
        }
        
        return $acc_discount;
    
    }

    /**
     * Returns the total accumulated catalog discounts on an item
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item
     * @return int negative discount amount
     */
    protected function _getTotalItemCatalogDiscount($item) {
        if ( ! $item->getQuoteId() || ! $item->getId() ) {
            return 0;
        }
        
        $row_total_before_disc = $item->getRowTotalBeforeRedemptions();
        $row_total = $item->getRowTotal();
        
        if ( $item->getRewardsCatalogDiscount() ) {
            $total_discount = $item->getRewardsCatalogDiscount();
        } else {
            if ( empty($row_total_before_disc) ) {
                $item->setRowTotal($item->getRowTotalBeforeRedemptions());
                $item->setRowTotalInclTax($item->getRowTotalBeforeRedemptionsInclTax());
                $total_discount = $this->_getRedeemer()->getTotalCatalogDiscount($item);
            } else {
                $total_discount = $item->getRowTotalBeforeRedemptions() - $item->getRowTotal();
            }
        }
        
        return $total_discount;
    
    }

}
 