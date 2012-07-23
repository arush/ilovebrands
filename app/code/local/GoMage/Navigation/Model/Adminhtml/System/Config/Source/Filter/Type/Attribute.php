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
	
class GoMage_Navigation_Model_Adminhtml_System_Config_Source_Filter_Type_Attribute{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
    	
    	$helper = Mage::helper('gomage_navigation');
    	
        return array(
            array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT, 'label' => $helper->__('Default')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE, 'label' => $helper->__('Image')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN, 'label' => $helper->__('Dropdown')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT, 'label' => $helper->__('Input')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER, 'label' => $helper->__('Slider')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT, 'label' => $helper->__('Slider and Input')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER, 'label' => $helper->__('Input an Slider')),
        	array('value'=>GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK, 'label' => $helper->__('In Block')),
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
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_IMAGE	=> $helper->__('Image'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_DROPDOWN => $helper->__('Dropdown'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT	=> $helper->__('Input'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER	=> $helper->__('Slider'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_SLIDER_INPUT => $helper->__('Slider and Input'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_INPUT_SLIDER => $helper->__('Input and Slider'),
        	GoMage_Navigation_Model_Layer::FILTER_TYPE_DEFAULT_INBLOCK => $helper->__('In Block'),
        );
    }

}