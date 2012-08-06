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
class FME_Manufacturers_Block_Adminhtml_Manufacturers_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('manufacturers_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('manufacturers')->__('Manufacturer Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('manufacturers')->__('Manufacturer Information'),
          'title'     => Mage::helper('manufacturers')->__('Manufacturer Information'),
          'content'   => $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tab_form')->toHtml(),
      ));
	  
	  $this->addTab('meta_section', array(
            'label'     => Mage::helper('manufacturers')->__('Meta Data'),
            'title'     => Mage::helper('manufacturers')->__('Meta Data'),
            'content'   => $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tab_meta')->toHtml(),
            
      ));
	  
	  $this->addTab('contact_section', array(
            'label'     => Mage::helper('manufacturers')->__('Contact Details'),
            'title'     => Mage::helper('manufacturers')->__('Contact Details'),
            'content'   => $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tab_contactdetails')->toHtml(),
            
      ));
	
	  $this->addTab('products_section', array(
			'label'     => Mage::helper('manufacturers')->__('Attach With Products'),
			'url'       => $this->getUrl('*/*/products', array('_current' => true)),
			'class'     => 'ajax',
	  ));
	  
	 /* $id  = $this->getRequest()->getParam('id');
	  if($id !=0) {
		  $this->addTab('assigned_products', array(
				'label'     => Mage::helper('manufacturers')->__('Manufacturer Products'),
				'title'     => Mage::helper('manufacturers')->__('Manufacturer Products'),
				'content'   => $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tab_products')->toHtml(),
				
		  ));
	  }*/
     
      return parent::_beforeToHtml();
  }
}