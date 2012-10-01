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

class GoMage_Feed_Block_Adminhtml_Attributes_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
	
	
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        
        if(Mage::registry('gomage_custom_attribute')){
        	$item = Mage::registry('gomage_custom_attribute');
        }else{
        	$item = new Varien_Object();
        }
        
        $this->setForm($form);
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => $this->__('Attribute Information')));
     	
     	$fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
        ));
        
        $headerBar = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Feed Pro Help'),
                    'class'   => 'go',
                    'id'      => 'feed_pro_help',
                    'onclick' => 'window.open(\'http://www.gomage.com/faq/extensions/feed-pro\')'
                ));
        
        $fieldset->setHeaderBar(
                    $headerBar->toHtml()
                ); 
   
        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => $this->__('Dynamic Attribute Code'),
            'title'     => $this->__('Dynamic Attribute Code'),
            'required'  => true,
            'class'		=> 'validate-code',
            'note'		=> $this->__('For internal use. Must be unique with no spaces'),
        ));
        
    	$fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $this->__('Name'),
            'title'     => $this->__('Name'),
            'required'  => true,
            'note'		=>$this->__('e.g. "Custom Price", "Google Category"...')
        ));
        
        if(!$item->getType() && $this->getRequest()->getParam('type')){
        	$item->setType($this->getRequest()->getParam('type'));
        }
        
        if($item->getId()){
        
        	$form->setValues($item->getData());
        
        }
        
        
        return parent::_prepareForm();
        
    }
    
  
}