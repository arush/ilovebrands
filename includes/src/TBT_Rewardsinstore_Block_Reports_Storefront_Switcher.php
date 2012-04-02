<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
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
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Reports_Storefront_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_storefrontIds;
    
    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardsinstore/reports/storefront/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Storefronts'));
    }
    
    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }
    
    /**
     * Get store groups for specified website
     *
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }
    
    /**
     * Get store views for specified store group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return array
     */
    public function getStorefronts($website)
    {
        //if (!$group instanceof Mage_Core_Model_Store_Group) {
        //    $group = Mage::app()->getGroup($group);
        //}
        //$stores = $group->getStores();
        if (!$website instanceof Mage_Core_Model_Website) {
        	$website = Mage::app()->getWebsite($website);
        }
        $storefronts = Mage::getModel('rewardsinstore/storefront')->getCollection()
        	->addFieldToFilter('website_id', $website->getId());
        if ($storeIds = $this->getStoreIds()) {
            foreach ($storefronts as $storeId => $store) {
                if (!in_array($storeId, $storeIds)) {
                    unset($storefronts[$storeId]);
                }
            }
        }
        return $storefronts;
    }
    
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, 'website' => null, 'group' => null, 'storefront' => null));
    }
    
    public function getWebsiteId()
    {
    	return $this->getRequest()->getParam('website');
    }
    
    public function getGroupId()
    {
    	return $this->getRequest()->getParam('group');
    }
    
    public function getStorefrontId()
    {
        return $this->getRequest()->getParam('storefront');
    }
    
    public function setStorefrontIds($storefrontIds)
    {
        $this->_storefrontIds = $storefrontIds;
        return $this;
    }
    
    public function getStorefrontIds()
    {
        return $this->_storefrontIds;
    }
    
    public function isShow()
    {
        return !Mage::app()->isSingleStoreMode();
    }
    
    protected function _toHtml()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return parent::_toHtml();
        }
        return '';
    }
    
    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
}
