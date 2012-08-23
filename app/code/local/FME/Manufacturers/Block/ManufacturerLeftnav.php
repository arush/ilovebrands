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

class FME_Manufacturers_Block_ManufacturerLeftnav extends Mage_Catalog_Block_Product_Abstract
{

	const VIEW_TYPE = 'manufacturers/leftnav/viewtype';
	protected function _tohtml()
    {
		 if (Mage::getStoreConfig(self::VIEW_TYPE) == "ddl") {
			 $this->setTemplate("manufacturers/manufacturersddl.phtml");
		 } elseif(Mage::getStoreConfig(self::VIEW_TYPE) == "dl") {
			 $this->setTemplate("manufacturers/leftnav.phtml");
		 } else {
			 $this->setTemplate("manufacturers/manufacturersddl.phtml");
		 }

		$this->setLinksforProduct();
		return parent::_toHtml();
    }
	
	 public function getAllLeftManufacturers()     
	{ 
		$Limit = 'manufacturers/leftnav/numberofmanufacturers';
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
								->addStoreFilter(Mage::app()->getStore(true)->getId())
								->addFieldToFilter('main_table.status', 1)
								->addOrder(Mage::helper('manufacturers')->getLeftManufacturerSort(), Mage::helper('manufacturers')->getLeftManufacturerOrder())
								->setPageSize(Mage::getStoreConfig($Limit))
								->getData();	
		return $collection;
	}
}
