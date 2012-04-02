<?php

/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsPlat_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isEnabled() {
        return true;
    }

	/**
	 * 
	 * 
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @param $positive : if true, the number will be positive even if the rule is a redemption
	 * @return string
	 */
	public function getPointsString($rule, $product, $positive=true) {	
		$pts = $this->getPointsForProduct($product);
		if($pts < 0 && $positive) {
			$pts = $pts * -1;
		}
		$str = Mage::helper('rewards')->getPointsString(array(
			$rule->getPointsCurrencyId() => $pts
		));
		return $str;
	}
}
