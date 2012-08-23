<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Manufacturers_Block_Adminhtml_Manufacturers_Edit_Tab_Contactdetails extends Mage_Adminhtml_Block_Widget_Form
{
    
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('contactdetails_fieldset', array('legend' => Mage::helper('manufacturers')->__('Contact Details')));
             
			$fieldset->addField('m_contact_name', 'text', array(
			  'label'     => Mage::helper('manufacturers')->__('Contact Person Name'),
			  'required'  => false,
			  'name'      => 'm_contact_name',
			));
			
			$fieldset->addField('m_contact_phone', 'text', array(
			  'label'     => Mage::helper('manufacturers')->__('Phone'),
			  'required'  => false,
			  'name'      => 'm_contact_phone',
			));
			
			$fieldset->addField('m_contact_fax', 'text', array(
			  'label'     => Mage::helper('manufacturers')->__('Fax'),
			  'required'  => false,
			  'name'      => 'm_contact_fax',
			));
			
			$fieldset->addField('m_contact_email', 'text', array(
			  'label'     => Mage::helper('manufacturers')->__('Email'),
			  'required'  => false,
			  'name'      => 'm_contact_email',
			));
			
			$fieldset->addField('m_contact_address', 'editor', array(
			  'name'      => 'm_contact_address',
			  'label'     => Mage::helper('manufacturers')->__('Address'),
			  'title'     => Mage::helper('manufacturers')->__('Address'),
			  'style'     => 'width:275px; height:100px;',
			  'wysiwyg'   => false,
			  'required'  => false,
			));
        
		
		if ( Mage::getSingleton('adminhtml/session')->getGalleryData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getManufacturersData());
			Mage::getSingleton('adminhtml/session')->setManufacturersData(null);
		} elseif ( Mage::registry('manufacturers_data') ) {
			$form->setValues(Mage::registry('manufacturers_data')->getData());
		}
		
        return parent::_prepareForm();
        
    }
    
  
}