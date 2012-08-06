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

class FME_Manufacturers_Model_Manufacturers extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('manufacturers/manufacturers');
    }
	
	/**
     * Retrieve related products
     *
     * @return array
     */
    public function getManufacturerRelatedProducts($manufacturerId)
    {
				
			$manufacturer_productsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
			$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
					  ->addManufacturerFilter($manufacturerId);
					  
			$collection->getSelect()
            ->joinLeft(array('related' => $manufacturer_productsTable),
                        'main_table.manufacturers_id = related.manufacturers_id'
                )
			->order('main_table.manufacturers_id');
			
			return $collection->getData();

    }
	
	public function checkManufacturer($id)
    {
        return $this->_getResource()->checkManufacturer($id);
    }
	
	/*
     * Delete Manufacturer Stores
     * @return Array
     */
	public function deleteManufacturerStores($id){
		return $this->getResource()->deleteManufacturerStores($id);
		
	}
	
	/*
     * Delete Manufacturer Product Links
     * @return Array
     */
	public function deleteManufacturerProductLinks($id){
		return $this->getResource()->deleteManufacturerProductLinks($id);
		
	}
	
	/*
     * Delete Manufacturer Attributes
     * @return Array
     */
	public function deleteBrandsAttributes($id){
		return $this->getResource()->deleteBrandsAttributes($id);
		
	}
	
	/**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier)
    {
        return $this->_getResource()->checkIdentifier($identifier);
    }
	
	public function getProductsCount($id) {
		
		
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection()
							->addFieldToFilter('main_table.manufacturers_id', $id);
		$collection->getSelect()
		->joinLeft(array('mp' => $collection->getTable('manufacturers_products')),
					'main_table.manufacturers_id=mp.manufacturers_id AND (mp.product_id != 0)',
					array(
						'products_count' => new Zend_Db_Expr('count(mp.product_id)'),
						'products_ids' => new Zend_Db_Expr('GROUP_CONCAT(mp.product_id)'),
					)
			)
		->group('main_table.manufacturers_id');
		
		return $collection;
	}
	
}
