<?php

/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsOnly_Helper_Config extends Mage_Core_Helper_Abstract {

    public function isEnabled() {
        return true;
    }
    
    
    public function showRedeemerWhenNotLoggedIn() {
    	return true;
    }
    
    public function showRedeemer() {
    	return false;
    }
    
    public function showProductViewEarning() {
    	return false;
    }
    
    public function forceRedemptions() {
    	return true;
    }
    
    public function forceOneRedemption() {
    	return true;
    }
    
    public function showPointsAsPrice() {
    	return true;
    }
    
    public function showCartDistributionsWhenZero()
    {
    	return false;
//        return Mage::getStoreConfigFlag('rewards/display/showCartDistributionsWhenZero');
    }
    
    public function replaceGrandTotalWithPoints() {
    	return true;
    }
    
    public function showItemDiscounts() {
    	return false;
    }
    

    
    
    public function getRedemptionSelectionAlgorithm() {
    	$class =  Mage::getConfig()->getNode('global/rewards/rule_selection_alogorithm/model');
    	$model = Mage::getModel($class);
    	if($model) {
    		return $model;
    	} else {
    		throw new Exception("Rule selection algorithm model '$class' doesn't exist.  Please check your config.xml");
    	}
    }
    
    public function disableCheckoutsIfNotEnoughPoints() {
    	return true;
    }
    
    
    public function getDefaultPointsUnknownMsg() {
		return $this->__("See product details");
    }
    
    
    public function canUseRedemptionsIfNotLoggedIn() {
    	return false;
    }

}
