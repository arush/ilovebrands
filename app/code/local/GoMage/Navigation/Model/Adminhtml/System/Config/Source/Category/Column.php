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
 * @since        Class available since Release 1.0
 */
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Category_Column
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
                    array('value'=>1, 'label' => $helper->__('1')),
                	array('value'=>2, 'label' => $helper->__('2')),
                	array('value'=>3, 'label' => $helper->__('3')),
                	array('value'=>4, 'label' => $helper->__('4')),        	
                	array('value'=>5, 'label' => $helper->__('5')),        	
                ); 
    	}
    	
        return $this->_options;
    	
    }
}