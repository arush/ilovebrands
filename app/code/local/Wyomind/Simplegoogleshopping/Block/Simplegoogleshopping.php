<?php
class Wyomind_Simplegoogleshopping_Block_Simplegoogleshopping extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {	
    	
    		return parent::_prepareLayout();
    }
    
     public function getSimplegoogleshopping()     
     { 
        if (!$this->hasData('simplegoogleshopping')) {
            $this->setData('simplegoogleshopping', Mage::registry('simplegoogleshopping'));
        }
        return $this->getData('simplegoogleshopping');
        
    }
}