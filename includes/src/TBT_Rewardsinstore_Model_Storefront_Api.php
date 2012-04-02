<?php
class TBT_Rewardsinstore_Model_Storefront_Api extends TBT_Rewardsinstore_Model_Api_Abstract
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
