<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 3.0
 */
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Category_Template
      extends Mage_Eav_Model_Entity_Attribute_Source_Abstract{

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {    	
    	$helper = Mage::helper('gomage_navigation');

    	if (!$this->_options) {
    	    $this->_options = array(
                    array('value'=>1, 'label' => $helper->__('Style 1')),
                	array('value'=>2, 'label' => $helper->__('Style 2')),                	        	
                ); 
    	}
    	
        return $this->_options;
    	
    }
}