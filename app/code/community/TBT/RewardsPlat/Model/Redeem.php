<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
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
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Redeem
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsPlat_Model_Redeem extends TBT_Rewards_Model_Redeem {

    /**
     * Retenders the item's redemption rules and final row total and returns it.
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array a map of the new item redemption data: 
     * 					array('redemptions_data'=>{...}, 'row_total'=>float)
     */
    protected function getUpdatedRedemptionData($item, $do_incl_tax = true) {
        // Step 1: Create a map of usability for all applied redemptions
        //echo "$item->getRedeemedPointsHash()";
        $redeemed_points = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());

        // Prepare data from item and initalize counters
        if ($item->getQuote())
            $store_currency = round($item->getQuote()->getStoreToQuoteRate(), 4);
        if ($item->getOrder()) {
            $store_currency = round($item->getOrder()->getStoreToQuoteRate(), 4);
        }

        if ($item->hasCustomPrice()) {
            $product_price = (float) $item->getCustomPrice() * $store_currency;
        } else {
        	//@nelkaake -a 17/02/11: We need to use our own calculation because the one that was set by the 
        	// rest of the Magento system is rounded.
			if(Mage::helper('tax')->priceIncludesTax() && $item->getPriceInclTax()) {
		      	$product_price = $item->getPriceInclTax() / (1 + $item->getTaxPercent()/100);
		    } else {
	            $product_price = (float) $item->getPrice() * $store_currency;
		    }
	    
        }
        if ($item->getParentItem() || sizeof($redeemed_points) == 0) {
            return array(
                'redemptions_data' => array(),
                'row_total_incl_tax' => $item->getRowTotalInclTax(),
                'row_total' => $item->getRowTotal(),
            );
        }
        
        $total_qty = $item->getQty();
        $total_qty_redeemed = 0.0000;
        $row_total = 0.0000;
        $new_redeemed_points = array();
        $ret = array();

        // Loop through and apply all our rules.
        foreach ($redeemed_points as $key => &$redemption_instance) {
            $redemption_instance = (array) $redemption_instance;
        
    		
    		$effect = $redemption_instance[self::POINTS_EFFECT];
    		if(!isset($redemption_instance[self::POINTS_USES])) {
    			$redemption_instance[self::POINTS_USES] = 1;
    		}
            
        	//@nelkaake WDCA BEGIN adds Sweet Tooth one-redemption mode functionality ///////BEGIN///////////
    		if($this->isOneRedemptionMode()) { 
    			// The total quantity that the redemption instance should apply to should be equal
    			// to the total quantity in the item
    			$item_qty = $item->getQty();
    			$applic_qty = $item_qty;
    			$redemption_instance[TBT_Rewards_Model_Redeem::POINTS_APPLICABLE_QTY] = $item_qty;
    		} else {
        	//@nelkaake WDCA END adds Sweet Tooth one-redemption mode functionality //////////END///////
    			$applic_qty = $redemption_instance[self::POINTS_APPLICABLE_QTY];
        	//@nelkaake WDCA BEGIN adds Sweet Tooth one-redemption mode functionality /////////BEGIN/////////
    		}
        	//@nelkaake WDCA END adds Sweet Tooth one-redemption mode functionality /////////END/////////
            
            $effect = $redemption_instance[self::POINTS_EFFECT];
            if (!isset($redemption_instance[self::POINTS_USES]))
                $redemption_instance[self::POINTS_USES] = 1;
            $uses = (int) $redemption_instance[self::POINTS_USES];

            $total_qty_remain = $total_qty - $total_qty_redeemed;
            if ($total_qty_remain > 0) {
                if ($total_qty_remain < $applic_qty) {
                    $applic_qty = $total_qty_remain;
                    $redemption_instance[TBT_Rewards_Model_Redeem::POINTS_APPLICABLE_QTY] = $applic_qty;
                }
                
                $price_after_redem = $this->getPriceAfterEffect($product_price, $effect, $item);
              
                $row_total += $applic_qty * (float) $price_after_redem;
                $total_qty_redeemed += $applic_qty;
                $new_redeemed_points[] = $redemption_instance;
            } else {
                $redemption_instance[TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY] = 0;
                $redemption_instance[TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES] = 1; // used once by default
                unset($redeemed_points[$key]);
            }
        }

        $ret['redemptions_data'] = $new_redeemed_points;

        // Add in the left over products that perhaps weren't affected by qty adjustment.
        $total_qty_remain = ($total_qty - $total_qty_redeemed);
        if ($total_qty_remain < 0) {
            $total_qty_remain = 0;
            $total_qty_redeemed = $total_qty;
            //throw new Exception("Redemption rules may be overlapping.  Please notify the store administrator of this error.");
        }
        $row_total += $total_qty_remain * (float) $product_price;
		
        $ret['row_total'] = $row_total;
        $ret['row_total_incl_tax'] = $row_total;
        return $ret;
    }
    
    
    public function isOneRedemptionMode() {
    	$points_as_price =  Mage::helper('rewardsplat/config')->showPointsAsPrice();
    	$one_redemption_only = Mage::helper('rewardsplat/config')->forceOneRedemption();
    	$force_redemptions = Mage::helper('rewardsplat/config')->forceRedemptions();
    	$is_one_redemption_mode = ($points_as_price && $one_redemption_only && $force_redemptions);
    	return $is_one_redemption_mode;
    }
	
    
}
