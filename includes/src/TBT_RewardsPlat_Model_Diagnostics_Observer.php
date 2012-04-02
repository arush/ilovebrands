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
 * @package    TBT_RewardsPlat
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsPlat_Model_Diagnostics_Observer extends TBT_RewardsPlat_Model_Controller_Observer {

    /**
     * This observer method fires on the event that the Sweet Tooth Rewards
     * DiagnosticsController is used to run the reinstall database script.
     * 
     * After the rewards database has had a chance to rebuild then call the reinstall db action
     * for the platinum diagnostics
     */
    public function reinstalldb($observer) {
    	Mage::getSingleton('rewardsplat/diagnostics_database')->reinstalldb();
        return $observer;
    }
    
    public function _onPreDispatch($controller) {
        if(!$this->_isManageControl($controller)) return $this;
        
        $this->checkEnterpriseReward();
        
        return $this;
    }
    
    protected function checkEnterpriseReward() {
        if(!$this->_hasModuleEnabled('Enterprise_Reward')) {
            return $this;
        }
        
        if($this->_attemptToDisableModule('Enterprise_Reward') ) {
            Mage::getSingleton('core/session')->addNotice("You had the Magento Enterprise default Rewards module enabled while Sweet Tooth is enabled and installed so we disabled it for you.");
        } else {
            Mage::getSingleton('core/session')->addError("You have the Magento Enterprise default Rewards module enabled. Sweet Tooth may not function as expected until you disable it. To disable it, change the 'active' tag in app/etc/modules/Enterprise_Reward.xml to 'false'.");
        }
        
        return $this;
    }
    
    
    protected function _hasModuleEnabled($module_key) {
        $mod_cfg = Mage::getConfig ()->getModuleConfig ( $module_key );
        
        if(!$mod_cfg) return false;
        
        $is_active = $mod_cfg->is('active', true);
        
        return $is_active;
    }
    
    protected function _attemptToDisableModule($module_key) {
        try {
            $enterprise_cfg_file = Mage::getBaseDir('etc'). DS . 'modules' . DS . $module_key . '.xml';
            
            if(!file_exists($enterprise_cfg_file)) {
                return false;
            }
            
            $cfg_contents = file_get_contents($enterprise_cfg_file);
            $new_cfg_contents = str_replace('<active>true</active>', '<active>false</active>', $cfg_contents);
            
            file_put_contents($enterprise_cfg_file, $new_cfg_contents);
            
        } catch(Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return true if the controller is of TBT_Rewards_Manage type   
     */       
    protected function _isManageControl($controller) {
        $manage_controls = array(
            'TBT_Rewards_Manage_'
        );
        $class_name =  get_class($controller);
        foreach($manage_controls as $manage_control) {
            if(strpos($class_name, $manage_control) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    
}
