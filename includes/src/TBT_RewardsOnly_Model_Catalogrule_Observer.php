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
 * Catalog Rule Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsOnly_Model_Catalogrule_Observer extends TBT_Rewards_Model_Catalogrule_Observer
{
	
    /**
     * Gets triggered on the  event to append points information
        Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item'=>$result, 'product'=>$product));
     * @param unknown_type $o
     */
    public function appendPointsQuoteAfterAdd($o) {
		$item = $o->getEvent ()->getQuoteItem ();
		$product = $o->getEvent ()->getProduct ();
		//@nelkaake -a 17/02/11: Use the generic request.  
		$request = Mage::app ()->getRequest ();
		
		if (! $request || ! $product || ! $item)
			return $this;
		
		if ($item->getParentItem ()) {
			$item = $item->getParentItem ();
		}
		
		
		$apply_rule_id = $request->getParam ( 'redemption_rule' );
		$apply_rule_uses = $request->getParam ( 'redemption_uses' );
		$qty = $request->getParam ( 'qty' );
		
		
//	    BEGIN Rewards Only Mode Feature
    
		$rp = Mage::getModel('rewardsonly/catalog_product')->wrap2($product);
		$rule_selector = $this->getRedemptionSelectionAlgorithm();
		$rule_selector->init(Mage::getSingleton('rewards/session')->getSessionCustomer(), $rp);
		if($rule_selector->hasRule()) {
    		if(Mage::helper('rewardsonly/config')->forceRedemptions()) {
    			$customer = Mage::getSingleton('rewards/session')->getSessionCustomer();
        		$apply_rule_id = $this->getApplyRedemptionId($request, $customer, $product);
        		if(!empty($apply_rule_id)) {
        			$apply_rule_uses = $this->getApplyRedemptionUses($request);
    		
    				if(empty($apply_rule_uses)) {
    					throw new Exception("You must use your points to purchase this product.");
    				}
        		}
    		}
		}
		
//	    END Rewards Only Mode Feature
    
    	try {
	    	Mage::getSingleton('rewards/catalogrule_saver')->appendPointsToQuote($product, $apply_rule_id, $apply_rule_uses, $qty, $item);
    	} catch (Exception $e) {    	
    		Mage::helper('rewards')->notice($e->getMessage());
    		Mage::logException($e);
    		Mage::getSingleton('core/session')->addError(
    			Mage::helper('rewards')->__("An error occured trying to apply the redemption while adding the product to your cart: ") 
    			. $e->getMessage()
    		);	
    	}
    	
    	return $this;
    	
    }
	/**
	 * Loads the rule selection algorithm model from config.
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Selection_Algorithm_Abstract
	 */
	protected function getRedemptionSelectionAlgorithm() {
		return Mage::helper('rewardsonly/config')->getRedemptionSelectionAlgorithm();
	}
	
	
    protected function getApplyRedemptionId($request, $customer, $product) {
    	if(Mage::helper('rewardsonly/config')->showRedeemer()) {
    		$apply_rule_id = $request->getParam('redemption_rule');
    	} else {
    		$rule_selector = $this->getRedemptionSelectionAlgorithm();
			$rule_selector->init($customer, $product);
			if(!$rule_selector->hasRule()) {
				return null;
			}
			$rule = $rule_selector->getRule();
			$apply_rule_id  = $rule->getId();
    	}
    	return $apply_rule_id;
    }
    
    protected function getApplyRedemptionUses($request) {
    	if(Mage::helper('rewardsonly/config')->showRedeemer()) {
    		$apply_rule_uses = $request->getParam('redemption_uses');
    	} else {
			$apply_rule_uses = 1;
    	}
    	return $apply_rule_uses;
    }
    
	/**
	 * Ensures that the given product model is a rewards catlaog product
	 *
	 * @param mixed $product
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	protected function ensureProduct($product) {
		if($product instanceof Mage_Catalog_Model_Product) {
			$product = TBT_Rewards_Model_Catalog_Product::wrap($product);
		}
		return $product;
	}
}
