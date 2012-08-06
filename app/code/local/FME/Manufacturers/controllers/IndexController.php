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
 
class FME_Manufacturers_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {	
		$itemsPerPage = Mage::getStoreConfig('manufacturers/manufacturers/items_per_page');
		$showAlphaHeading = Mage::getStoreConfig('manufacturers/manufacturers/show_manufacturers_with_alphabets');
		
		$brandsSortBy = Mage::helper('manufacturers')->getManufacturerSort();
		$brandsOrderBy = Mage::helper('manufacturers')->getManufacturerOrder();
		
		if($showAlphaHeading){
			$brandsSortBy = "main_table.m_name";
			$brandsOrderBy = "ASC";
		}
		
		$startWith  = $this->getRequest()->getParam('start');
		
		if(isset($startWith)) {
			$startWith = strtolower($startWith);
			$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
								->addStoreFilter(Mage::app()->getStore(true)->getId())
								->addFieldToFilter('m_name',array('like'=>$startWith.'%'))
								->addFieldToFilter('main_table.status', 1)
								->addOrder($brandsSortBy, $brandsOrderBy)
								->getData();
		} else {
			$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
								->addStoreFilter(Mage::app()->getStore(true)->getId())
								->addFieldToFilter('main_table.status', 1)
								->addOrder($brandsSortBy, $brandsOrderBy)
								->getData();
		}

		if($showAlphaHeading){
			$itemsPerPage=999999999;
		}
		
		// Use paginator
		if ( $itemsPerPage != 0 ) {		
			$paginator = Zend_Paginator::factory((array)$collection);
			$paginator->setCurrentPageNumber((int)$this->_request->getParam('page', 1))
					  ->setItemCountPerPage($itemsPerPage);
			Mage::register('items', $paginator);
		} else {
			Mage::register('items', $collection);
		}
				   
    	$this->loadLayout();   
		$this->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('manufacturers/manufacturers/layout'));
		$this->renderLayout();
    }	
}
