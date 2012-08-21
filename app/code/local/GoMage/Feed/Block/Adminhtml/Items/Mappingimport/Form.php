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

class GoMage_Feed_Block_Adminhtml_Items_Mappingimport_Form extends Mage_Adminhtml_Block_Widget_Form{
  	
     protected function _prepareForm(){
  	  
		$form = new Varien_Data_Form(array(
	    	'id'        =>  'edit_form',
	    	'action'    =>  $this->getUrl('*/*/*', array('id' => $this->getRequest()->getParam('id'))),
	    	'method'    =>  'post',
	    	'enctype'   =>  'multipart/form-data'
		));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
}