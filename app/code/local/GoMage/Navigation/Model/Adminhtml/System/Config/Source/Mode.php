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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Mode{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        
    	$helper = Mage::helper('gomage_navigation');
    	
        $options = array(
            array('value' => 0, 'label'=>$helper->__('Default')),
        ); 
        
    	$websites = $helper->getAvailavelWebsites();
        
        if(!empty($websites)){
        	$options[] = array('value' => 1, 'label'=>$helper->__('Advanced Navigation'));
        } 
    	
        return $options;
    }

}