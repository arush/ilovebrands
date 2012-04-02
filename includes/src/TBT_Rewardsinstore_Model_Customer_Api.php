<?php
class TBT_Rewardsinstore_Model_Customer_Api extends TBT_Rewardsinstore_Model_Api_Abstract
{
    protected $_allowedParams = array(
        'storefront_id', 'firstname', 'lastname', 'email' /* TODO: ... */);
    
    protected $_mapAttributes = array(
        'customer_id' => 'entity_id');
    
    protected $_required = array(
        'create' => array(
            'storefront_id', 'firstname', 'lastname', 'email' /* TODO: ... */));
    
    public function create($customerData)
    {
        $customerData = $this->_prepareInput($customerData);
        
        try {
            $this->_checkRequirements('create', $customerData);
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $this->__("You must include the '%s' field to create an Instore Customer.", $ex->getMessage()));
        }
        
        try {
            $customer = Mage::getModel('customer/customer');
            foreach ($customerData as $key => $value) {
                $customer->setData($key, $value);
                break;
            }
            $customer->save();
        } catch (Exception $ex) {
            $this->_fault('data_invalid', $ex->getMessage());
        }
        
        return $customer->getId();
    }
}
