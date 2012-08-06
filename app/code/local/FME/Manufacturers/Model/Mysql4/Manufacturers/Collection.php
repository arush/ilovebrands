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

class FME_Manufacturers_Model_Mysql4_Manufacturers_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('manufacturers/manufacturers');
    }
	
	/**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Mage_Cms_Model_Mysql4_News_News_Collection
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
            array('store_table' => $this->getTable('manufacturers_store')),
            'main_table.manufacturers_id = store_table.manufacturers_id',
            array()
        )
        ->where('store_table.store_id in (?)', array(0, $store));

        return $this;
    }
	
	public function prepareSummary()
	{
			$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$manufacturersTable),'*')
				->where('status = ?', 1)
				->order('created_time','asc');;
			return $this;
	}
	
	public function getManufacturers()
	{
			$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$manufacturersTable),'*')
				->where('status = ?', 1)
				->order('created_time','asc');
			return $this;
	}
	
	public function getDetail($manufacturer_id)
	{
		$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$this->setConnection($this->getResource()->getReadConnection());
		$this->getSelect()
			->from(array('main_table'=>$manufacturersTable),'*')
			->where('manufacturers_id = ?', $manufacturer_id);
		return $this;
	}
	
	public function getManufacturerBlock()
	{
			$manufacturersTable = Mage::getSingleton('core/resource')->getTableName('manufacturers');
			$this->setConnection($this->getResource()->getReadConnection());
			$this->getSelect()
				->from(array('main_table'=>$manufacturersTable),'*')
				->where('status = ?', 1)
				->order('created_time DESC')
				->limit(5);
			return $this;
	}
	
    public function toOptionArray()
    {
        return $this->_toOptionArray('manufacturers_id', 'title');
    }
	
    public function addManufacturerFilter($manufacturers)
    {
        if (is_array($manufacturers)) {
            $condition = $this->getConnection()->quoteInto('main_table.manufacturers_id IN(?)', $manufacturers);
        }
        else {
            $condition = $this->getConnection()->quoteInto('main_table.manufacturers_id=?', $manufacturers);
        }
        return $this->addFilter('manufacturers_id', $condition, 'string');
    }
}