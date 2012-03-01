<?php
class TBT_Rewardsinstore_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function exampleMethod($param1, $param2 = 1)
    {
        $this->_faultIfNoInstore();
        
        // Process inputs
        
        return "exampleReturnValue";
    }
    
    public function storefrontList($filters)
    {
        if (isset($filters['url_code'])) {
            $filters['code'] = $filters['url_code'];
        }
        if (isset($filters['enabled'])) {
            $filters['is_active'] = !$filters['enabled'] || $filters['enabled'] == 'false' ? 0 : 1;
        }
        
        try {
            $list = Mage::getModel('rewardsinstore/api_storefront')->getList($filters);
        } catch (Exception $ex) {
            $this->_fault('storefront_exception', $ex->getMessage());
        }
        
        return $list;
    }
    
    public function storefrontCreate($storefrontData)
    {
        try {
            if (!isset($storefrontData['website_id'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Website ID to create a storefront."));
            } else if (!isset($storefrontData['name'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Storefront Name to create a storefront."));
            } else if (!isset($storefrontData['street'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Street Address to create a storefront."));
            } else if (!isset($storefrontData['country_id'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Country ID to create a storefront."));
            } else if (!isset($storefrontData['city'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a City to create a storefront."));
            } else if (!isset($storefrontData['postcode'])) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Postal/Zip Code to create a storefront."));
            }
            
            /* TODO: if country_id requires a region ...
             * if (!isset($storefrontData['region'])) {
             *  throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Region to create a storefront."));
             * }
             */
        } catch (Exception $ex) {
            $this->_fault('insufficient_input_exception', $ex->getMessage());
        }
        
        if (isset($storefrontData['url_code'])) {
            $storefrontData['code'] = $storefrontData['url_code'];
        } else {
            // TODO: autogenerate URL Code
        }
        
        $storefrontData['is_active'] = 1;
        if (isset($storefrontData['enabled'])) {
            $storefrontData['is_active'] = !$storefrontData['enabled'] || $storefrontData['enabled'] == 'false' ? 0 : 1;
        }
        
        try {
            $id = Mage::getModel('rewardsinstore/api_storefront')->create($storefrontData);
        } catch (Exception $ex) {
            $this->_fault('storefront_exception', $ex->getMessage());
        }
        
        return $id;
    }
    
    public function storefrontInfo($storefrontId, $attributes = array())
    {
        
    }
    
    public function storefrontUpdate($storefrontId, $storefrontData)
    {
        try {
            if (!$storefrontId) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Storefront ID to update a storefront."));
            }
            if (!is_array($storefrontData)) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply an array of storefront data to update a storefront."));
            }
        } catch (Exception $ex) {
            $this->_fault('insufficient_input_exception', $ex->getMessage());
        }
        
        if (isset($storefrontData['url_code'])) {
            $storefrontData['code'] = $storefrontData['url_code'];
        }
        
        if (isset($storefrontData['enabled'])) {
            $storefrontData['is_active'] = !$storefrontData['enabled'] || $storefrontData['enabled'] == 'false' ? 0 : 1;
        }
        
        try {
            $success = Mage::getModel('rewardsinstore/api_storefront')->update($storefrontId, $storefrontData);
        } catch (Exception $ex) {
            $this->_fault('storefront_exception', $ex->getMessage());
        }
        
        return $success;
    }
    
    public function storefrontDelete($storefrontId)
    {
        try {
            if (!$storefrontId) {
                throw new Exception(Mage::helper('rewardsinstore')->__("You must supply a Storefront ID to delete a storefront."));
            }
        } catch (Exception $ex) {
            $this->_fault('insufficient_input_exception', $ex->getMessage());
        }
        
        try {
            $success = Mage::getModel('rewardsinstore/api_storefront')->delete($storefrontId);
        } catch (Exception $ex) {
            $this->_fault('storefront_exception', $ex->getMessage());
        }
        
        return $success;
    }
    
    public function pointsTransferList($filters)
    {
        if (isset($filters['transfer_id'])) {
            $filters['rewards_transfer_id'] = $filters['transfer_id'];
        }
        if (isset($filters['status'])) {
            $statuses = Mage::getModel('rewards/transfer_status')->getOptionArray();
            if ($key = array_search($filters['status'], $statuses)) {
                $filters['status'] = $key;
            }
        }
        
        try {
            $list = Mage::getModel('rewardsinstore/api_transfer')->getList($filters);
        } catch (Exception $ex) {
            $this->_fault('transfer_exception', $ex->getMessage());
        }
        
        return $list;
    }
    
    /**
     * TODO: Not entirely sure if this method is necessary... I don't think this model can even get
     * loaded if the module isn't activated.
     */
    protected function _faultIfNoInstore()
    {
        if (Mage::getConfig()->getModuleConfig('TBT_Rewardsinstore')->is('active', 'false')) {
            $this->_fault('api_usage_exception', Mage::helper('rewardsinstore')->__("Sweet Tooth Instore must be installed on the server in order to use the Sweet Tooth Instore API."));
        }
        
        return $this;
    }
}
