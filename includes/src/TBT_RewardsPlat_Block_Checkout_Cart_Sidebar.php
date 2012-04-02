<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Block_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
	


    
    protected function _toHtml() {
    	$show_me = Mage::helper('rewardsplat/config')->replaceGrandTotalWithPoints();
    	if($show_me) {
        	$this->setTemplate('rewards/checkout/cart/sidebar.phtml');	
    	}
    	return parent::_toHtml();
    }
	
    
    public function getPointsSpent(){
    	$str = $this->_getRewardsSess()->getTotalPointsSpendingAsStringList();
        return $str;
    }
    
	
    /**
     * Fetchtes the rewards cofnig helper
     *
     * @return TBT_Rewards_Helper_Config
     */
    public function getCfgHelper() {
    	return Mage::helper('rewardsplat/config');
    }
    
    /**
     * Fetches the rewards session.
     *
     * @return TBT_Rewards_Model_Session
     */
    private function _getRewardsSess() {
    	return Mage::getSingleton('rewards/session');
    }
    
    /**
     * Fetches the checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession() {
    	return Mage::getSingleton('checkout/session');
    }
    
}