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

class GoMage_Feed_Block_Adminhtml_Items_Mappingimport_Tab_File extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $this->__('Import'),
            		'type'		=> 'submit',
                    'class'     => 'save',
            		'onclick'	=> 'editForm.submit();return false;'
                    ))
                );
        return parent::_prepareLayout();
    }
    
	
	protected function _prepareForm(){
        
        $form = new Varien_Data_Form();
        
        $this->setForm($form);
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => $this->__('Import Mapping')));
     
    	$fieldset->addField('mappingfile', 'file', array(
            'name'      => 'mappingfile',
            'label'     => $this->__('Settings File'),
            'title'     => $this->__('Settings File'),
            'required'  => true,
        ));
        
        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));
        
        return parent::_prepareForm();
        
    }
    
  
}