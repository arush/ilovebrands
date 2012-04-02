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
 * Catalog Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsPlat_Model_Catalogrule_Rule extends TBT_Rewards_Model_Catalogrule_Rule
{

	
	/**
	 * Loads in a catalogrule and returns a points catalogrule
	 *
	 * @param Mage_CatalogRule_Model_Rule $catalogrule
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	public static function wrap(Mage_CatalogRule_Model_Rule $catalogrule) {
       	$pointsrule = Mage::getModel('rewards/catalogrule_rule');
       	$pointsrule->setData($catalogrule->getData());
       	$pointsrule->setId($catalogrule->getId());
		return $pointsrule;
	}
	
	/**
	 * Returns the base price disposition on the given price.
	 *
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @return float
	 */
	public function getDispositionOnProduct($product) {
		 $adjusted_price = Mage::helper('rewards')->priceAdjuster(
		 	$product->getFinalPrice(), 
		 	$this->getEffect()
		 );
		$price_disp_base = $product->getFinalPrice() - (float)$adjusted_price;
		return $price_disp_base;
	}
	
	/**
	 * 
	 * 
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @param $positive : if true, the number will be positive even if the rule is a redemption
	 * @return string
	 */
	public function getPointsString($product, $positive=true) {	
		$pts = $this->getPointsForProduct($product);
		if($pts < 0 && $positive) {
			$pts = $pts * -1;
		}
		$str = Mage::helper('rewards')->getPointsString(array(
			$this->getPointsCurrencyId() => $pts
		));
		return $str;
	}
	
	/**
	 * Fetches a calculated points amount for a given product
	 * DO NOT USE getPointsAmount()!!! use this instead!!!
	 *
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @return integer
	 */
	public function getPointsForProduct(&$product) {
		$points = $product->getCatalogPointsForRule($this);
		if($points) {
			return $points['amount'];
		} else {
			return 0;
		}
	}
	
	
}