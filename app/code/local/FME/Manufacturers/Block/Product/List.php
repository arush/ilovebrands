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
class FME_Manufacturers_Block_Product_List extends Mage_Catalog_Block_Product_List //Mage_Catalog_Block_Product_Abstract
{


    protected $_productCollection;
    protected $_sort_by;
	protected $_manufacturerId;
  
	public function _prepareLayout()
    {
		
	  //Get Manfacturer
		$id  = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('manufacturers/manufacturers')->load($id);
		
	  if ($head = $this->getLayout()->getBlock('head')) {
		    
			if($model["m_manufacturer_page_title"] == '') {
				$head->setTitle(Mage::helper('manufacturers')->getListPageTitle());
			} else {
				$head->setTitle($model["m_manufacturer_page_title"]);
			}
			
			if($model["m_manufacturer_meta_keywords"] == '') {
				$head->setKeywords(Mage::helper('manufacturers')->getListMetaKeywords());
			} else {
				$head->setKeywords($model["m_manufacturer_meta_keywords"]);
			}
			
			if($model["m_manufacturer_meta_description"] == '') {
				$head->setDescription(Mage::helper('manufacturers')->getListMetaDescription());
			} else {
				$head->setDescription($model["m_manufacturer_meta_description"]);
			}
	  }
	
	  if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('manufacturers')->__('Home'),
                'title'=>Mage::helper('manufacturers')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));			
			
			$breadcrumbsBlock->addCrumb(Mage::helper('manufacturers')->getListIdentifier().'_home', array(
						'label' => Mage::helper('manufacturers')->getListIdentifier(), 
						'title' => Mage::helper('manufacturers')->getListIdentifier(), 
						'link' => Mage::helper('manufacturers')->getUrl()));
			
			$breadcrumbsBlock->addCrumb('view', array(
                'label'=>Mage::helper('manufacturers')->__($model->getMName())				                
               
            )); 
        }
		
		return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $toolbar = $this->getToolbarBlock();
        $toolbar->removeOrderFromAvailableOrders('position');
        return $this;

    }


    protected function _getProductCollection()
    {
				
        if (is_null($this->_productCollection)) {
			
			$id  = $this->getRequest()->getParam('id');
			
			$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
			$manufacturersProductsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
			$sqry = "SELECT mp.product_id,mp.manufacturers_id FROM ".$manufacturersTable." m 
					INNER JOIN ".$manufacturersProductsTable." AS mp ON m.manufacturers_id = mp.manufacturers_id
					WHERE m.manufacturers_id = ".$id;
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->query($sqry);
			$prds = $select->fetchAll();	
			$productIds = array();
			$i = 0; 
			foreach ($prds as $_manufacturer ) :
				$productIds[$i] = $_manufacturer["product_id"];
				$i++;
			endforeach;
		
			$result = array_unique($productIds);		
	
			$collection = Mage::getResourceModel('catalog/product_collection');
            $attributes = Mage::getSingleton('catalog/config')
                ->getProductAttributes();
            $collection->addAttributeToSelect($attributes)
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter();

            $collection->addIdFilter($result);
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->_productCollection = $collection;
	
        }
        return $this->_productCollection;
    }

    
    protected function _toHtml()
    {
        if ($this->_getProductCollection()->count()){
            return parent::_toHtml();
        }
        return parent::_toHtml();
    }
	
	public function getCrumbtitle() {	 
	 
	 	$id  = $this->getRequest()->getParam('id');
	 	$model  = Mage::getModel('manufacturers/manufacturers')->load($id);
		$title = $model->getMName();
		return $title;
	}

}
