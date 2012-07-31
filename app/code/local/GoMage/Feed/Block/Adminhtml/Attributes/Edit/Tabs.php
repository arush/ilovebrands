<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Feed_Block_Adminhtml_Attributes_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct(){
    	
        parent::__construct();
        $this->setId('gomage_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Attribute Information'));
        
    }
    
    protected function _prepareLayout(){
        
        //if(($type = $this->getRequest()->getParam('code', null)) || (Mage::registry('gomage_custom_attribute') && ($code = Mage::registry('gomage_custom_attribute')->getAttributeCode()))){
        
	        $this->addTab('main_section', array(
	            'label'     =>  $this->__('Attribute Information'),
	            'title'     =>  $this->__('Attribute Information'),
	            'content'   =>  $this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit_tab_main')->toHtml(),
	        ));
	        
	        $this->addTab('data_section', array(
	            'label'     =>  $this->__('Conditions and Values'),
	            'title'     =>  $this->__('Conditions and Values'),
	            'content'   =>  $this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit_tab_data')
	            				->setTemplate('gomage/feed/attribute/edit/data.phtml')->toHtml(),
	        ));
	        
	    /*
        }else{
        	
        	$this->addTab('main_section', array(
                'label'     => $this->__('Attribute information'),
                'title'     => $this->__('Attribute information'),
                'content'   => $this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit_tab_choose')->toHtml(),
            ));
        	
        }
        */
        if($tabId = addslashes(htmlspecialchars($this->getRequest()->getParam('tab')))){
        	
        	$this->setActiveTab($tabId);
        }
        
        
        return parent::_beforeToHtml();
        
    }
       
}