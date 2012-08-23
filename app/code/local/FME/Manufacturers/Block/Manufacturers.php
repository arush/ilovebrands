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
class FME_Manufacturers_Block_Manufacturers extends Mage_Core_Block_Template
{
	//protected $_defaultToolbarBlock = 'manufacturers/manufacturers_toolbar';
	
	public function _prepareLayout()
    {
		
		 if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('manufacturers')->__('Home'),
                'title'=>Mage::helper('manufacturers')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));
			$breadcrumbsBlock->addCrumb('manufacturers', array(
                'label' => Mage::helper('manufacturers')->getListIdentifier()
            ));
        }
		
		  
		if ($head = $this->getLayout()->getBlock('head')) {
			$head->setTitle(Mage::helper('manufacturers')->getListPageTitle());
			$head->setDescription(Mage::helper('manufacturers')->getListMetaDescription());
			$head->setKeywords(Mage::helper('manufacturers')->getListMetaKeywords());
		}
	 
		 return parent::_prepareLayout();
    }
    
     public function getItems()     
     { 
       	if ( !$this->hasData('items') ) {
			$this->setData('items', Mage::registry('items'));
		}
		return $this->getData('items');
        
    }
	
}