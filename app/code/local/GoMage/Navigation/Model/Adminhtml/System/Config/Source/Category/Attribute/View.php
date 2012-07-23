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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Category_Attribute_View 
		extends Mage_Eav_Model_Entity_Attribute_Source_Abstract{

	const TEXT = 1;
	const IMAGE = 2;
	const TEXT_IMAGE = 3;
	
    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>self::TEXT, 'label' => $helper->__('Text')),
        	array('value'=>self::IMAGE, 'label' => $helper->__('Image')),
        	array('value'=>self::TEXT_IMAGE, 'label' => $helper->__('Text and Image')),        	        	        	
        );
    	
    }
        
}