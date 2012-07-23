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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Category_View{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>'disabled', 'label' => $helper->__('Disabled')),
        	array('value'=>'menu_bar', 'label' => $helper->__('Menu Bar')),
        	array('value'=>'left_column', 'label' => $helper->__('Left Column')),
        	array('value'=>'right_column', 'label' => $helper->__('Right Column')),        	
        	
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
            'disabled' => $helper->__('Disabled'),
            'menu_bar' => $helper->__('Menu Bar'),
            'left_column' => $helper->__('Left Column'),
        	'right_column' => $helper->__('Right Column'),        	
        	
        );
    }

}