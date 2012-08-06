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

class FME_Manufacturers_Block_ManufacturerLogo extends Mage_Catalog_Block_Product_Abstract
{

	const DISPLAY_CONTROLS = 'manufacturers/productpagelogo/enabled';
	protected function _tohtml()
    {
		 if ($this->getFromXml()=='yes'&&!Mage::getStoreConfig(self::DISPLAY_CONTROLS))
            return parent::_toHtml();

		$this->setLinksforProduct();
		$this->setTemplate("manufacturers/manufacturerlogo.phtml");
		return parent::_toHtml();
    }
    
}