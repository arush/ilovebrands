<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied category filter
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Category constructor.
 */
class Mana_Filters_Model_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category {
    /**
     * Apply category filter to product collection
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mana_Filters_Block_Filter_Category $filterBlock
     * @return  Mana_Filters_Block_Filter_Category
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see app/code/core/Mage/Catalog/Model/Layer/Filter/Mage_Catalog_Model_Layer_Filter_Category::apply()
     */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int) $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        $this->_categoryId = $filter;

        $category   = $this->getCategory();
        Mage::register('current_category_filter', $category);

        $this->_appliedCategory = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);

			$this->getLayer()->getState()->addFilter($this->_createItemEx(array(
            	'label' => $this->_appliedCategory->getName(), 
            	'value' => $filter,
            	'm_selected' => true,
            )));
        }

        return $this;
    }
	/**
     * Returns all values currently selected for this filter
     */
//    public function getMSelectedValues() {
//    	$values = Mage::app()->getRequest()->getParam($this->_requestVar);
//		return $values ? explode('_', $values) : array();    
//    }
    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     * This method is cloned from method _createItem() in parent class (method body was pasted from parent class 
     * completely rewritten.
     * Standard method did not give us possibility to initialize non-standard fields. 
     */
    protected function _createItemEx($data)
    {
        return Mage::getModel('mana_filters/item')
            ->setData($data)
            ->setFilter($this);
    }
    /** 
     * Initializes internal array of in-memory representations of options of a filter
     * @return Mana_Filters_Model_Filter_Attribute
     * @see Mage_Catalog_Model_Layer_Filter_Abstract::_initItems()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
        	// MANA BEGIN
            $items[] = $this->_createItemEx($itemData);
            // MANA END
        }
        // MANA BEGIN: enable additional filter item processing
    	/* @var $ext Mana_Filters_Helper_Extended */ $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $items = $ext->processFilterItems($this, $items);
        // MANA END
        $this->_items = $items;
        return $this;
    }
    protected function _getCategoryItemsData($category, $products, $categories = null)
    {
    	if (!$categories) {
			$categories = $category->getChildrenCategories();
	        $products->addCountToCategories($categories);
    	}
        $data = array();
        foreach ($categories as $category) {
        	if ($category->getIsActive() && $category->getProductCount()) {
            	$data[] = array(
                	'label' => Mage::helper('core')->htmlEscape($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
            		'm_selected' => $category->getId() == $this->getCategory()->getId()
                );
            }
        }
        return $data;
    }
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $categoty   = $this->getCategory();
            /** @var $categoty Mage_Catalog_Model_Categeory */
            Mana_Core_Profiler::start('mln', __CLASS__, __METHOD__, '$categoty->getChildrenCategories()');
            $categories = $categoty->getChildrenCategories();
            Mana_Core_Profiler::stop('mln', __CLASS__, __METHOD__, '$categoty->getChildrenCategories()');
            
            $this->getLayer()->getProductCollection()
                ->addCountToCategories($categories);

            $data = array();
            foreach ($categories as $category) {
                if ($category->getIsActive() && $category->getProductCount()) {
                    $data[] = array(
                        'label' => Mage::helper('core')->htmlEscape($category->getName()),
                        'value' => $category->getId(),
                        'count' => $category->getProductCount(),
                    );
                }
            }
            $data = $this->_getCategoryItemsData($this->getCategory(), $this->getLayer()->getProductCollection(), $categories);
            //if (!count($data)) { // no child categories having products
            //	$category = $this->getCategory()->getParentCategory();
            //	$products = clone ($this->getLayer()->getProductCollection());
            //	$products->addCategoryFilter($category);
            //	$data = $this->_getCategoryItemsData($category, $products);
            //}
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
    public function getRemoveUrl() {
    	$query = array($this->getRequestVar()=>$this->getResetValue());
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    public function getName() {
        return $this->getFilterOptions()->getName();
    }
}