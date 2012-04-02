<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_Filters module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Filters_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Recognizes block type based on its class. 
	 * OO purists would say that kind of ifs should be done using virtual functions. Here we ignore OO-ness and 
	 * micro performance penalty for the sake of clarity and keeping logic in one file.
	 * @param Mage_Catalog_Block_Layer_Filter_Abstract $block
	 * @return string
	 */
	public function getBlockType($block) {
		if ($block instanceof Mana_Filters_Block_Filter_Attribute) return 'attribute';
		elseif ($block instanceof Mana_Filters_Block_Filter_Category) return 'category';
		elseif ($block instanceof Mana_Filters_Block_Filter_Decimal) return 'decimal';
		elseif ($block instanceof Mana_Filters_Block_Filter_Price) return 'price';
		else throw new Exception('Not implemented');
	}
	/**
	 * Return unique filter name. 
	 * OO purists would say that kind of ifs should be done using virtual functions. Here we ignore OO-ness and 
	 * micro performance penalty for the sake of clarity and keeping logic in one file.
	 * @param Mage_Catalog_Model_Layer_Filter_Abstract $model
	 * @return string
	 */
	public function getFilterName($model) {
		if ($model instanceof Mana_Filters_Model_Filter_Category) return 'category';
		else return $model->getAttributeModel()->getAttributeCode();
	}
	// INSERT HERE: helper functions that should be available from any other place in the system
	public function getJsPriceFormat() {
		return $this->formatPrice(0);
	}
	public function formatPrice($price) {
		$store = Mage::app()->getStore();
        if ($store->getCurrentCurrency()) {
            return $store->getCurrentCurrency()->formatPrecision($price, 0, array(), false, false);
        }
        return $price;
	}
	
	protected $_filterOptionsCollection;
    protected $_filterSearchOptionsCollection;
    protected $_filterAllOptionsCollection;
	public function getFilterOptionsCollection($allCategories = false) {
	    $request = Mage::app()->getRequest();
	    if ($request->getModuleName() == 'catalogsearch' && $request->getControllerName() == 'result' && $request->getActionName() == 'index' ||
	        $request->getModuleName() == 'manapro_filterajax' && $request->getControllerName() == 'search' && $request->getActionName() == 'index')
	    {
            if (!$this->_filterSearchOptionsCollection) {
                $this->_filterSearchOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
                        ->addColumnToSelect('*')
                        ->addStoreFilter(Mage::app()->getStore())
                        ->setOrder('position', 'ASC');
            }
            Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterSearchOptionsCollection));
            return $this->_filterSearchOptionsCollection;
        }
		if ($allCategories) {
			if (!$this->_filterAllOptionsCollection) {
				$this->_filterAllOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
		        	->addColumnToSelect('*')
		        	->addStoreFilter(Mage::app()->getStore())
		        	->setOrder('position', 'ASC');
			}
			Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterAllOptionsCollection));
			return $this->_filterAllOptionsCollection;
		}
		else {
			if (!$this->_filterOptionsCollection) {
				Mana_Core_Profiler::start('mln', __CLASS__, __METHOD__, '$productCollection->getSetIds()');
				$setIds = Mage::getSingleton('catalog/layer')->getProductCollection()->getSetIds();
				Mana_Core_Profiler::stop('mln', __CLASS__, __METHOD__, '$productCollection->getSetIds()');
				$this->_filterOptionsCollection = Mage::getResourceModel('mana_filters/filter2_store_collection')
		        	->addFieldToSelect('*')
		        	->addCodeFilter($this->_getAttributeCodes($setIds))
                    ->addStoreFilter(Mage::app()->getStore())
		        	->setOrder('position', 'ASC');
			}
            Mage::dispatchEvent('m_before_load_filter_collection', array('collection' => $this->_filterOptionsCollection));
            return $this->_filterOptionsCollection;
		}
	}
	protected function _getAttributeCodes($setIds) {
		/* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */ 
		$collection = Mage::getResourceModel('catalog/product_attribute_collection');
		$collection->setAttributeSetFilter($setIds);
		$select = $collection->getSelect()
			->reset(Zend_Db_Select::COLUMNS)
			->columns('attribute_code');
		return array_merge($collection->getConnection()->fetchCol($select), array('category'));
	}
	public function markLayeredNavigationUrl($url, $routePath, $routeParams) {
		if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_filters')) {
			$routeParams['_nosid'] = true;
			//if (Mage::getSingleton('core/url')->getUrl($routePath, $routeParams) == Mage::getSingleton('core/url')->getRouteUrl($routePath, $routeParams)) {
				$url .= (strpos($url, '?') === false) ? '?m-layered=1' : '&m-layered=1';
			//}
			//else {
			//	$url = str_replace('?m-layered=1', '', $url);
			//	$url = str_replace('&m-layered=1', '', $url);
			//}
		}
		return $url;
	}
    public function getClearUrl($markUrl = true, $clearListParams = false) {
        $filterState = array();
        foreach (array_merge(Mage::getSingleton('catalog/layer')->getState()->getFilters(), Mage::getSingleton('catalogsearch/layer')->getState()->getFilters()) as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        if ($clearListParams) {
            $filterState = array_merge($filterState, array(
              'dir' => null,
              'order' => null,
              'p' => null,
              'limit' => null,
              'mode' => null,
            ));
        }
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $filterState['m-layered'] = null;
        $params['_query'] = $filterState;
        $result = Mage::getUrl('*/*/*', $params);
        if ($markUrl) {
            $result = $this->markLayeredNavigationUrl($result, '*/*/*', $params);
        }
        return $result;
    }
    public function getActiveFilters() {
        $filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }
        return $filters;
    }
    public function resetProductCollectionWhereClause($select) {
        $preserved = new Varien_Object(array('preserved' => array()));
        $where = $select->getPart(Zend_Db_Select::WHERE);
        Mage::dispatchEvent('m_preserve_product_collection_where_clause', compact('where', 'preserved'));
        $preserved = $preserved->getPreserved();
        foreach ($where as $key => $condition) {
            if (!in_array($key, $preserved)) {
                unset($where[$key]);
            }
        }
        $where = array_values($where);
        if (isset($where[0]) && strpos($where[0], 'AND ') === 0) {
            $where[0] = substr($where[0], strlen('AND '));
        }
        $select->setPart(Zend_Db_Select::WHERE, $where);
    }
}