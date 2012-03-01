<?php
class TBT_Rewardsinstore_Model_Customer_Attribute_Source_Storefront extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = Mage::getResourceModel('rewardsinstore/storefront_collection');
            $this->_options = $collection->load()->toOptionArray();
            //if ('created_in' == $this->getAttribute()->getAttributeCode()) {
            //    array_unshift($this->_options, array('value' => '0', 'label' => Mage::helper('customer')->__('Admin')));
            //}
        }
        return $this->_options;
    }
    
    public function getOptionText($value)
    {
        if (!$value) {
            $value = "0";
        }
        $isMultiple = false;
        if (strpos($value, ",")) {
            $isMultiple = true;
            $value = explode(",", $value);
        }
        
        $this->getAllOptions();
        
        if ($isMultiple) {
            $values = array();
            foreach ($value as $val) {
                $values[] = $this->_options[$val];
            }
            
            return $values;
        }
        else {
            return $this->_options[$value];
        }
        
        return false;
    }
}
