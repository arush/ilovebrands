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
 * Product View Points
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsPlat_Block_Product_Price extends Mage_Catalog_Block_Product_Price
{
	protected $_product = null;
	
	protected function _prepareLayout() {
		return parent::_prepareLayout();
	}
	
	
    /**
     * Retrieve current product model
     *
     * @return TBT_Rewards_Model_Catalog_Product
     */
    public function getProduct()
    {
    	if(!$this->isEnabled()) {
    		return parent::getProduct();
    	}
       	$p = $this->_getData('product');
       	if(empty($p)) {
       		$p = Mage::registry('product');
       	}
        if (!empty($p)) {
        	if($p instanceof TBT_Rewards_Model_Catalog_Product) {
        		$this->_product = $p;
        	} else {
	            $this->_product = Mage::getModel('rewards/catalog_product')->load($p->getId());
        	}
            Mage::unregister('product');
            Mage::register('product', $this->_product);
        } else {
        	$this->_product = Mage::getModel('rewards/catalog_product');
        }
        return $this->_product;
    }
    
    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
    
    	if(!$this->isEnabled())	{
    		return parent::_toHtml();
    	}
    	
    	if(strpos($this->getTemplate(), "price.phtml") === false) {
    		return parent::_toHtml();
    	}
    	
    	
		$rule_selector = $this->getRedemptionSelectionAlgorithm();
		$rule_selector->init($this->getCurrentCustomer(), $this->getProduct());
		if($rule_selector->hasRule()) {
			$this->setTemplate('rewardsplat/product/price.phtml');
		}
        return parent::_toHtml();
    }
    
	/**
	 * Fetches a string value of this product
	 *
	 * @return string
	 */
	public function getSimplePointsCost() {
		$points = $this->getProduct()->getSimplePointsCost($this->getCurrentCustomer());
		
		$points_str ="". $points; // cast the points model to a string 
		
		return $points_str ;
	}
	
	
	
	/**
	 * Loads the rule selection algorithm model from config.
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Selection_Algorithm_Abstract
	 */
	protected function getRedemptionSelectionAlgorithm() {
		return Mage::helper('rewardsplat/config')->getRedemptionSelectionAlgorithm();
	}
	
	
	
	/**
	 * TODO: This doesn't really disable the module, we need to extend the
	 * 		 correct class and abstract out the getRedeemableOptions() function.
	 *
	 * @return boolean
	 */
	public function isEnabled() {
		$show_points_as_price = Mage::helper('rewardsplat/config')->showPointsAsPrice();
		return $show_points_as_price;
	}
	
	
	/**
	 * Fetches any points redeemable options.
	 *
	 * @param Mage_Catalog_Model_Product $product [ = null ]
	 * @return array()
	 */
	public function getRedeemableOptions() {
		Varien_Profiler::start('TBT_REWARDS: Get Redeemable Options');
		$applicable_rules = array();
    	$product = $this->getProduct();
    	
    	try {
			$applicable_rules = $product->getRedeemableOptions($this->getCurrentCustomer(), $product);
    	} catch (Exception $e) {
    		die("An error occured trying to apply the redemption while adding the product to your cart: ". $e->getMessage());	
    	}
		Varien_Profiler::stop('TBT_REWARDS: Get Redeemable Options');
    	return $applicable_rules;
	}
	
	/**
	 * Fetches the current session customer, or false if the customer is not logged in.
	 *
	 * @return TBT_Rewards_Model_Customer the customer model or false if no customer is logged in.
	 */
	public function getCurrentCustomer() {
		if($this->customer == null) {
			if($this->_getRS()->isCustomerLoggedIn()) {
				$this->customer = $this->_getRS()->getSessionCustomer();
				if(!$this->customer->getId()) {
					$this->customer = false;
				}
			} else {
				$this->customer = false;
			}
		}
		return $this->customer;
	}
	
	
	
	/**
	 * Fetches a catalogrule
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	protected function _getRule($rule_id) {
		$rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);
		return $rule;
	}
	

    /**
     * Fetches the rewards session model
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSess() {
    	return Mage::getSingleton('rewards/session');
    }
    
    /**
     * Fetches the rewards session model
     * alias to _getRewardsSess()
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRS() {
    	return $this->_getRewardsSess();
    }
    
}