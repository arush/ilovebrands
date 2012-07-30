<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{	
 	
  protected function _prepareForm()
    {
         $form = new Varien_Data_Form(
                array(
                  'id' => 'edit_form',
                  'action' => $this->getUrl('*/*/save', array('simplegoogleshopping_id' => $this->getRequest()->getParam('simplegoogleshopping_id'))),
                  'method' => 'post',
                	
                 )
              );
			 
		  $form->setUseContainer(true);
          $this->setForm($form);
		
		  return parent::_prepareForm();
     
   }
}
?>