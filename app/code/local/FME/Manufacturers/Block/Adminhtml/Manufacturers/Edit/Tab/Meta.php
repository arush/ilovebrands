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
 
class FME_Manufacturers_Block_Adminhtml_Manufacturers_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('meta_fieldset', array('legend' => Mage::helper('manufacturers')->__('Meta Data')));
        
		$fieldset->addField('m_manufacturer_page_title', 'text', array(
		  'label'     => Mage::helper('manufacturers')->__('Page Title'),
		  'required'  => false,
		  'name'      => 'm_manufacturer_page_title',
		));
		
    	$fieldset->addField('m_manufacturer_meta_keywords', 'editor', array(
            'name'		=> 'm_manufacturer_meta_keywords',
            'label'		=> Mage::helper('manufacturers')->__('Keywords'),
            'title'		=> Mage::helper('manufacturers')->__('Meta Keywords'),
    		'required'	=> false
        ));

    	$fieldset->addField('m_manufacturer_meta_description', 'editor', array(
            'name'		=> 'm_manufacturer_meta_description',
            'label'		=> Mage::helper('manufacturers')->__('Description'),
            'title'		=> Mage::helper('manufacturers')->__('Meta Description'),
    		'required'	=> false
        ));
        
		$data = Mage::registry('manufacturers_data');		
	 	$form->setValues($data);
        return parent::_prepareForm();
        
    }
    
  
}