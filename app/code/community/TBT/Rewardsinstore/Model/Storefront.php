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
class TBT_Rewardsinstore_Model_Storefront extends TBT_Rewardsinstore_Model_Loyalty_Rewardsinstore 
{
    const URL_TYPE_STOREFRONT = 'storefront/';
    
    protected $_eventPrefix = 'rewardsinstore_storefront';
    protected $_eventObject = 'storefront';
    
    protected function _construct()
    {
        $this->_init('rewardsinstore/storefront');
    }   
    
    // TODO: move this to the collection (Wow, I must have written this back in the day)
    public function toOptionArray()
    {
        $storefronts = $this->getCollection();
        
        $options = array();
        foreach ($storefronts as $storefront) {
            $id = $storefront->getId();
            $name = $storefront->getName();
            if ($id !== null) {
                $options[] = array('value' => $id, 'label' => $name);
            }
        }
        return $options;
    }
    
    /**
     * Gets the default store view of this
     * storefront's website.
     *
     * @return Mage_Core_Model_Store
     */
    public function getDefaultStore()
    {
        $website = Mage::getModel('core/website')->load($this->getWebsiteId());
        
        if (!$website->getId()) {
            return null;
        }
        
        $store = $website->getDefaultStore();
        
        return $store->getId() ? $store : null;
    }
    
    // TODO: add $fields array parameter to specify which fields are inluded in the result
    public function getFormattedAddress($format = 'oneline')
    {
        $address = '';
        
        if ($format == 'oneline') {
            $fields = array(
                $this->getStreet(),
                $this->getCity(),
                $this->getRegionName(true),
                $this->getCountryName(),
                );
            
            $address = implode(', ', $fields);
        } else {
            Mage::logException(
                new Exception('Invalid format given for storefront address'));
        }
            
        return $address;
    }
    
    /**
     * Returns the Storefront Country as text
     *
     * @return string
     */
    public function getCountryName()
    {
        if ($countryId = $this->getCountryId()) {
            if ($country = Mage::getModel('directory/country')->loadByCode($countryId)) {
                return $country->getName();
            }
        }
        return '';
    }
    
    /**
     * Returns the Storefront Region as text
     *
     * @return string
     */
    public function getRegionName($short = false)
    {
        if ($regionId = $this->getRegionId()) {
            if ($region = Mage::getModel('directory/region')->load($regionId)) {
                return $short ? $region->getCode() : $region->getName();
            }
        }
        
        if ($regionText = $this->getRegion()) {
            return $regionText;
        }
        return '';
    }
    
    public function generateUrlCode()
    {
        $storefronts = $this->getCollection()
            ->addFieldToFilter('city', $this->getCity());
        $count = count($storefronts) + 1;
        $code = str_replace("'", "", str_replace(" ", "-", strtolower($this->getCity())));
        
        while ($storefronts->checkIfValueExists('code', $code . $count)) {
            $count++;
        }
        
        return $code . $count;
    }
    
    protected function _beforeSave()
    {
        // Autogenerate url code if it doesn't exist
        if (!$this->getCode()) {
            $this->setCode($this->generateUrlCode());
        }
        parent::_beforeSave();
    }
}
