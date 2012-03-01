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
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout Rewards Earned
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Checkout_Rewardearned extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/checkout/rewardearned.phtml' ); // TODO: move this to rewards.xml file.
	}
	
	public function getPointsEarned() {
		return $this->_getRewardsSess ()->getTotalPointsEarningAsStringList ();
	}
	
	/**
	 * Show the earnings row?
	 *
	 * @return boolean
	 */
	public function showEarnings() {
		$doShow = true;
		if ($this->_getRewardsSess ()->hasDistributions ()) {
			$doShow = true;
		} else {
			if (Mage::helper ( 'rewards/config' )->showCartDistributionsWhenZero ()) {
				$doShow = true;
			} else {
				$doShow = false;
			}
		}
		return $doShow;
	}
	
	/**
	 * Fetches the rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	private function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	protected function _toHtml() {
		if (Mage::helper ( 'rewards' )->isMultishipMode ()) {
			return '';
		} else {
			return parent::_toHtml ();
		}
	}

}
