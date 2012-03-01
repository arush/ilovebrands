<?php

/**
 * Every backend controller in this module should extend this class.
 * 
 * It contains general purpose functions for backend controllers, aswell
 * as compatibility fixes for different versions of Magento.
 *
 */
class TBT_Rewardsinstore_RewardsinstoreController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * This function does not exist previous to Magento 1.4.0.0
     */
    protected function _title($text = null, $resetIfExists = true) {
        
        if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        
        return $this;
    }
    
}