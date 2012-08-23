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
 
class FME_Manufacturers_ViewController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {    	
		$this->loadLayout();            
		$this->renderLayout();
    }
	
	public function setManufacturerAction() {
		
		$identifier  = $this->getRequest()->getParam('identifier');
		$url = Mage::helper('manufacturers')->getUrl($identifier);
		$_SESSION["manufactureridentifier"] = $identifier;
		echo $url;
		
	}
}