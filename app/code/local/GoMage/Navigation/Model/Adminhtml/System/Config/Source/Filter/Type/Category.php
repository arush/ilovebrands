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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Filter_Type_Category{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT, 'label' => $helper->__('Default')),
            array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_PRO, 'label' => $helper->__('Fly-Out')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE, 'label' => $helper->__('Image')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN, 'label' => $helper->__('Dropdown')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_PLAIN, 'label' => $helper->__('Plain')),
        	//array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_FOLDING, 'label' => $helper->__('Folding')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK, 'label' => $helper->__('In Block')),
        	//array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_ACCORDION, 'label' => $helper->__('Accordion')),
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
            GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT	=> $helper->__('Default'),
            GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_PRO	=> $helper->__('Fly-Out'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE	=> $helper->__('Image'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN => $helper->__('Dropdown'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_PLAIN => $helper->__('Plain'),
        	//GoMage_Navigation_Model_Layer::FILTER_TYPE_FOLDING => $helper->__('Folding'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK => $helper->__('In Block'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_ACCORDION => $helper->__('Accordion'),
        );
    }

}