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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Imagealign{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>'left', 'label' => $helper->__('Left')),
        	array('value'=>'right', 'label' => $helper->__('Right')),
        	array('value'=>'top', 'label' => $helper->__('Top')),
        	array('value'=>'bottom', 'label' => $helper->__('Bottom')),
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
            'left' => $helper->__('Left'),
            'right' => $helper->__('Right'),
            'top' => $helper->__('Top'),
        	'bottom' => $helper->__('Bottom'),
        );
    }

}