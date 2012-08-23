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

class FME_Manufacturers_Model_Mysql4_Manufacturers extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the manufacturers_id refers to the key field in your database table.
        $this->_init('manufacturers/manufacturers', 'manufacturers_id');
    }
	
	 protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('manufacturers_store'))
            ->where('manufacturers_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }
		
		//Get Category Ids
		$select = $this->_getReadAdapter()->select()
            ->from($this->getTable('manufacturers_products'))
            ->where('manufacturers_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $productsArray = array();
            foreach ($data as $row) {
                $productsArray[] = $row['product_id'];
            }
            $object->setData('product_id', $productsArray);
        }

        return parent::_afterLoad($object);
        
    }
	
	
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

		if (!$this->getIsUniqueIdentifier($object)) {
            Mage::throwException(Mage::helper('manufacturers')->__('Manufacturer Identifier already exist with same Identifier.'));
        }

        if ($this->isNumericIdentifier($object)) {
            Mage::throwException(Mage::helper('manufacturers')->__('Manufacturer Identifier cannot consist only of numbers.'));
        }

        return $this;
    }
	
	/**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		$condition = $this->_getWriteAdapter()->quoteInto('manufacturers_id = ?', $object->getId());
		//Get All Selected Products
		if(isset($_POST['productIds'])) {
			$this->_getWriteAdapter()->delete($this->getTable('manufacturers_products'), $condition);	
			
			$productIdsString = $_POST['productIds'];
			$productIdsString = trim($productIdsString, ',');	
			
			if(isset($_POST['productIds'])) {
				if($productIdsString != '') {
					$manufacturersProductsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
					$db = $this->_getWriteAdapter();
					try {
							$db->beginTransaction();
							$db->exec("DELETE FROM ".$manufacturersProductsTable." WHERE product_id in ($productIdsString)");
							$db->commit();
							
					} catch(Exception $e) {
						$db->rollBack();
						throw new Exception($e);
					}
				}
			}

			$productIds = explode(",", $productIdsString);	
			$Result = array_unique($productIds);
					
			 foreach ($Result as $_productId) {	 
			 
				$productsArray = array();
				$productsArray['manufacturers_id'] = $object->getId();
				$productsArray['product_id'] = $_productId;
				$this->_getWriteAdapter()->insert($this->getTable('manufacturers_products'), $productsArray);
			}
		}
		
	
		if(isset($_POST['stores'])) {
			 $this->_getWriteAdapter()->delete($this->getTable('manufacturers_store'), $condition);
			foreach ((array)$_POST['stores'] as $store) {
				$storeArray = array();
				$storeArray['manufacturers_id'] = $object->getId();
				$storeArray['store_id'] = $store;
				$this->_getWriteAdapter()->insert($this->getTable('manufacturers_store'), $storeArray);
			}
		}
    
        return parent::_afterSave($object);
        
    }
	
	 public function checkManufacturer($id)
    {

		$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$manufacturersProductsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
		$sqry = "SELECT * FROM ".$manufacturersTable." m 
				 INNER JOIN ".$manufacturersProductsTable." AS mp ON m.manufacturers_id = mp.manufacturers_id
				 WHERE mp.product_id = ".$id;


		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$select = $connection->query($sqry);
		$manufacturers = $select->fetchAll();
		return $manufacturers;
    }
	
	/**
	 *
	 * @Delete Manfacturer Stores
	 *
	 * @access public
	 *
	 * @param string $id
	 *
	 */
	public function deleteManufacturerStores($id){
	$manufacturersStoreTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_store');		
	$db = $this->_getWriteAdapter();
	try {
			$db->beginTransaction();
			$db->exec("DELETE FROM ".$manufacturersStoreTable." WHERE manufacturers_id = $id");
			$db->commit();
			
		} catch(Exception $e) {
			$db->rollBack();
			throw new Exception($e);
		}
	}
	
	/**
	 *
	 * @Delete Manfacturer Stores
	 *
	 * @access public
	 *
	 * @param string $id
	 *
	 */
	public function deleteManufacturerProductLinks($id){

	$manufacturersProductsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
	$db = $this->_getWriteAdapter();
	try {
			$db->beginTransaction();
			$db->exec("DELETE FROM ".$manufacturersProductsTable." WHERE manufacturers_id = $id");
			$db->commit();
			
		} catch(Exception $e) {
			$db->rollBack();
			throw new Exception($e);
		}
	}
	
	/*
	* Delete from magneto attributes option tables
	*/
	public function deleteBrandsAttributes($id){

		$eav_attribute_option_value_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
		$eav_attribute_option_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option');
		$catalog_product_entity_int = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
		$option_id = Mage::helper('manufacturers')->getBrandsOptionId($id);
		if($option_id){
		$db = $this->_getWriteAdapter();
			try {
				$db->beginTransaction();
				$db->exec("DELETE FROM ".$eav_attribute_option_table." WHERE option_id = ".$option_id);
				$db->exec("DELETE FROM ".$eav_attribute_option_value_table." WHERE option_id = ".$option_id);
				$db->exec("DELETE FROM ".$catalog_product_entity_int." WHERE value = ".$option_id);
				$db->commit();
				
			} catch(Exception $e) {
				$db->rollBack();
				throw new Exception($e);
			}
		}
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
		
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'manufacturers_id')
            ->where('main_table.identifier = ?', array($identifier))
            ->where('main_table.status = 1')
            ->order('main_table.manufacturers_id DESC');
						
        return $this->_getReadAdapter()->fetchOne($select);
    }
	
	public function getIsUniqueIdentifier(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getWriteAdapter()->select()
                ->from($this->getMainTable())
                ->where($this->getMainTable().'.identifier = ?', $object->getData('identifier'));
        if ($object->getId()) {
            $select->where($this->getMainTable().'.manufacturers_id <> ?',$object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }
        return true;
    }
		
	protected function isNumericIdentifier (Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }
		
}