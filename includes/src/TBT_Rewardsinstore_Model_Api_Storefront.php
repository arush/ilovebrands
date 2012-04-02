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
 * Instore Storefront API
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Api_Storefront extends TBT_Rewardsinstore_Model_Api_Abstract
{
    protected $_ignoredParams = array(
        'description', 'created_at', 'updated_at',
    );
    
    protected $_ignoredOutput = array(
        'description', 'sort_order'
    );
    
    protected $_mapAttributes = array(
        'url_code' => 'code',
        'enabled'  => 'is_active');
    
    protected $_required = array(
        'create' => array(
            'website_id', 'name', 'street', 'country_id', 'city'));
    
    protected $_booleans = array(
        'is_active');
    
    public function items($filters)
    {
        $filters = $this->_prepareInput($filters);
        
        try {
            $collection = Mage::getModel('rewardsinstore/storefront')->getCollection();
                        
            foreach ($filters as $key => $value) {
                $collection->addFieldToFilter($key, $value);
            }
            
            return $this->_prepareCollection($collection);
        } catch (Exception $ex) {
            $this->_fault('filters_invalid', $ex->getMessage());
        }
    }
    
    public function create($storefrontData)
    {
        $storefrontData = $this->_prepareInput($storefrontData);
        
        try {
            $this->_checkRequirements('create', $storefrontData);
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $this->__("You must include the '%s' field to create a Storefront.", $ex->getMessage()));
        }
        
        try {
            
            $country_id = $storefrontData['country_id'];
            $country = Mage::getModel('directory/country')->loadByCode($country_id);
            
            $region_ids = Mage::helper('rewardsinstore')->getCollectionIds($country->getRegions());
            
            /**
             * If there are regions available for this country then
             * a valid region must be specified.
             */
            if (count($region_ids)) {
                if (!isset($storefrontData['region'])) {
                    throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Region Id to create a storefront in this country."));
                }
                
                $region = $storefrontData['region'];
                
                $new_region = Mage::helper('rewardsinstore/directory')->getRegionInCountry($region, $country_id);
                $new_region_id = $new_region->getId();
                
                // Check if region id exists
                if (!$new_region_id) {
                    throw new Exception(Mage::helper('rewardsinstore')->__("Region '{$region}' does not exist."));
                }
                
                // Check that the region id is valid for given country
                if (!in_array($new_region_id, $region_ids)) {
                    throw new Exception(Mage::helper('rewardsinstore')->__("Region '{$region}' is not valid for country {$country_id}."));
                }
                
                // Set both region_id and region if we find one that's valid
                $storefrontData['region_id'] = $new_region_id;
                $storefrontData['region'] = $new_region->getName();
            }
            
            // Check if postcodes are required for specified country
            if (!Mage::helper('directory')->isZipCodeOptional($country_id)) {
                if (!isset($storefrontData['postcode'])) { 
                    throw new Exception(Mage::helper('rewardsinstore')->__("A postcode is required for addresses in country {$country_id}."));
                }
            }
            
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
        
        try {
            $storefront = Mage::getModel('rewardsinstore/storefront');
            
            foreach ($storefrontData as $key => $value) {
                $storefront->setData($key, $value);
            }
            
            // TODO: create some kind of _setDefaults method here
            $storefront->setCode(isset($storefrontData['code']) ? $storefrontData['code'] : $storefront->generateUrlCode());
            $storefront->setIsActive(isset($storefrontData['is_active']) ? $storefrontData['is_active'] : 1);
            $storefront->save();
            
            $id = $storefront->getId();
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
        
        return $id;
    }
    
    public function info($storefrontId, $attributes = array())
    {
        if (!$storefrontId) {
            $this->_fault('data_invalid', $this->__("You must supply the ID of the storefront to request."));
        }
        
        if (!is_array($attributes)) {
            $this->_fault('data_invalid', $this->__("The 'attributes' field must be an array."));
        }
        
        try {
            $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
            if (!$storefront->getId()) {
                $this->_fault('not_exists', $this->__("Storefront with ID={$storefrontId} does not exist."));
            }
            
            return $this->_prepareEntity($storefront, $attributes);
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
    }
    
    public function update($storefrontId, $storefrontData)
    {
        $storefrontData = $this->_prepareInput($storefrontData);
        
        if (!$storefrontId) {
            $this->_fault('data_invalid', $this->__("You must supply a Storefront ID to update a storefront."));
        }
        if (!is_array($storefrontData)) {
            $this->_fault('data_invalid', $this->__("You must supply an array of storefront data to update a storefront."));
        }
        
        try {
            $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
            if (!$storefront->getId()) {
                $this->_fault('not_exists', $this->__("Storefront with ID={$storefrontId} does not exist."));
            }
            
            foreach ($storefrontData as $key => $value) {
                $storefront->setData($key, $value);
            }
            $storefront->save();
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
        
        return true;
    }
    
    public function delete($storefrontId)
    {
        if (!$storefrontId) {
            $this->_fault('data_invalid', $this->__("You must supply a Storefront ID to delete a storefront."));
        }
        
        try {
            $storefront = Mage::getModel('rewardsinstore/storefront')->load($storefrontId);
            if (!$storefront->getId()) {
                $this->_fault('not_exists', $this->__("Storefront with ID={$storefrontId} does not exist."));
            }
            
            $storefront->delete();
        } catch (Exception $ex) {
            $this->_fault('not_deleted', $ex->getMessage());
        }
        
        return true;
    }
}
