<?php
class Evogue_CatalogEvent_Helper_Adminhtml_Event extends Mage_Core_Helper_Abstract {

    protected $_categories = null;

    protected $_inEventCategoryIds = null;

    public function getCategories() {
        if ($this->_categories === null) {
            $tree = Mage::getModel('catalog/category')->getTreeModel();
            /* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
            $tree->load(null, 2); // Load only to second level.
            $tree->addCollectionData(null, 'position');
            $this->_categories = $tree->getNodeById(Mage_Catalog_Model_Category::TREE_ROOT_ID)->getChildren();
        }
        return $this->_categories;
    }

    public function getCategoriesOptions($without = array(), $emptyOption = false) {
        $result = array();
        foreach ($this->getCategories() as $category) {
            if (! in_array($category->getId(), $without)) {
                $result[] = $this->_treeNodeToOption($category, $without);
            }
        }

        if ($emptyOption) {
            array_unshift($result, array(
                'label' => '' , 'value' => ''
            ));
        }
        return $result;
    }

    protected function _treeNodeToOption(Varien_Data_Tree_Node $node, $without) {

        $option = array();
        $option['label'] = $node->getName();
        if ($node->getLevel() < 2) {
            $option['value'] = array();
            foreach ($node->getChildren() as $childNode) {
                if (!in_array($childNode->getId(), $without)) {
                    $option['value'][] = $this->_treeNodeToOption($childNode, $without);
                }
            }
        } else {
            $option['value'] = $node->getId();
        }
        return $option;
    }

    public function searchInCategories($categories, $categoryId) {

        foreach ($categories as $category) {
            if ($category->getId() == $categoryId) {
                return $category;
            } elseif ($category->hasChildren() && ($foundCategory = $this->searchInCategories($category->getChildren(), $categoryId))) {
                return $foundCategory;
            }
        }
        return false;
    }

    public function getInEventCategoryIds() {

        if ($this->_inEventCategoryIds === null) {
            $collection = Mage::getModel('evogue_catalogevent/event')->getCollection();
            $this->_inEventCategoryIds = $collection->getColumnValues('category_id');
        }
        return $this->_inEventCategoryIds;
    }
}
