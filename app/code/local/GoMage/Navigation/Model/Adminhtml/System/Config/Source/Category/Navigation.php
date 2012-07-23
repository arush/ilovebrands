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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Category_Navigation{
   
    
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
    	$options = array(
            array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT, 'label' => $helper->__('Default')),
        ); 
        
        $websites = $helper->getAvailavelWebsites();
        
        if(!empty($websites)){
        	$options[] = array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_PLAIN, 'label' => $helper->__('Plain'));
        	$options[] = array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN, 'label' => $helper->__('Dropdown'));
        }
    	
        return $options;
    	
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionHash(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT => $helper->__('Default'),
            GoMage_Navigation_Model_Layer::FILTER_TYPE_PLAIN => $helper->__('Plain'),
            GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN => $helper->__('Dropdown'),        	        	        	
        );
    }

}