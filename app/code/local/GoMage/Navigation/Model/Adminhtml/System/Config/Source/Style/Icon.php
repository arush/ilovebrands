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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Style_Icon{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>'default', 'label' => $helper->__('Default')),
        	array('value'=>'blue', 'label' => $helper->__('Blue')),
        	array('value'=>'orange', 'label' => $helper->__('Orange')),
        	array('value'=>'blue-violet', 'label' => $helper->__('Blue violet')),
        	array('value'=>'pale-pink', 'label' => $helper->__('Pale pink')),
        	array('value'=>'green', 'label' => $helper->__('Green')),
        	array('value'=>'yellow', 'label' => $helper->__('Yellow')),
        	array('value'=>'red', 'label' => $helper->__('Red')),
        	array('value'=>'black', 'label' => $helper->__('Black')),
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
            'default' => $helper->__('Default'),
        	'blue' => $helper->__('Blue'),
        	'orange' => $helper->__('Orange'),
        	'blue-violet' => $helper->__('Blue violet'),
        	'pale-pink' => $helper->__('Pale pink'),
        	'green' => $helper->__('Green'),
        	'yellow' => $helper->__('Yellow'),
        	'red' => $helper->__('Red'),
        	'black' => $helper->__('Black'),
        );
    }

}