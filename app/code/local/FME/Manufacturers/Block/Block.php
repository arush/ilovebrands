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

class FME_Manufacturers_Block_Block extends Mage_Core_Block_Template
{

    public function getItems($limit = 4)     
	{ 
	
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
							->addStoreFilter(Mage::app()->getStore(true)->getId())
							->addFieldToFilter('main_table.status', 1)
							->addOrder('main_table.created_time', 'desc')
							->setPageSize($limit)
							->getData();
		return $collection;
        
	}
	
	 public function getAllManufacturers()     
	{ 
		
		$manufacturer_productsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		$catalog_product_entityTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
		$cataloginventory_stock_statusTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_status');
		$catalog_product_index_priceTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_price');
		$catalog_product_websiteTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_website');
	
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
					  ->addStoreFilter(Mage::app()->getStore(true)->getId())
					  ->addFieldToFilter('main_table.status', 1);
		$collection-> getSelect()-> joinleft(array('manufacturer_products' => $manufacturer_productsTable), 'main_table.manufacturers_id = manufacturer_products.manufacturers_id');
        $collection-> getSelect()-> joinleft(array('product_entity' => $catalog_product_entityTable), 'manufacturer_products.product_id = product_entity.entity_id', array(
		 'products_count' => new Zend_Db_Expr('count(product_entity.entity_id)')
		 ));
		$collection->getSelect()->group('main_table.manufacturers_id');
		$collection->getData();
		return $collection;
        
	}
	
	public function getManufacturersProductCount($mid)
	{
		$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$manufacturersProductsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
		$sqry = "SELECT mp.product_id,mp.manufacturers_id FROM ".$manufacturersTable." m 
				INNER JOIN ".$manufacturersProductsTable." AS mp ON m.manufacturers_id = mp.manufacturers_id
				WHERE m.manufacturers_id = ".$mid;
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
		return $collection->count();
		
	}
	
	 public function getFeaturedManufacturers()     
	{ 
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
							->addStoreFilter(Mage::app()->getStore(true)->getId())
							->addFieldToFilter('main_table.status', 1)
							->addFieldToFilter('main_table.m_featured', 1)
							->addOrder(Mage::helper('manufacturers')->getFeaturedManufacturerSort(), Mage::helper('manufacturers')->getFeaturedManufacturerOrder())
							->getData();
		return $collection;
        
	}
    
}
