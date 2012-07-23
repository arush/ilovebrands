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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Autoscrolling{

    
	const NO = 0;
	const BUTTON = 1;
	const AJAX = 2;
	
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>self::NO, 'label' => $helper->__('No')),
        	array('value'=>self::BUTTON, 'label' => $helper->__('Button')),
        	array('value'=>self::AJAX, 'label' => $helper->__('Ajax')),        	        	
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
            self::NO => $helper->__('No'),
            self::BUTTON => $helper->__('Button'),
            self::AJAX => $helper->__('Ajax'),        	
        );
    }

}