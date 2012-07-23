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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Shopby{
    
	const LEFT_COLUMN = 0;
	const CONTENT = 1;
	const RIGHT_COLUMN = 2;
		
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>self::LEFT_COLUMN, 'label' => $helper->__('Left Column')),
        	array('value'=>self::CONTENT, 'label' => $helper->__('Content')),        	        	        	
        	array('value'=>self::RIGHT_COLUMN, 'label' => $helper->__('Right Column')),
        );
    	
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionHash(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            self::LEFT_COLUMN => $helper->__('Left Column'),
            self::CONTENT => $helper->__('Content'), 
            self::RIGHT_COLUMN => $helper->__('Right Column'),                   	
        );
    }

}