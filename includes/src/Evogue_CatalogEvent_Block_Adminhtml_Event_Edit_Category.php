<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Edit_Category extends Mage_Adminhtml_Block_Catalog_Category_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('evogue/catalogevent/categories.phtml');
    }

    public function getTreeArray($parentId = null, $asJson = false, $recursionLevel = 3) {
        $result = array();
        if ($parentId) {
            $category = Mage::getModel('catalog/category')->load($parentId);
            if (!empty($category)) {
                $tree = $this->_getNodesArray($this->getNode($category, $recursionLevel));
                if (!empty($tree) && !empty($tree['children'])) {
                    $result = $tree['children'];
                }
            }
        } else {
            $result = $this->_getNodesArray($this->getRoot(null, $recursionLevel));
        }
        if ($asJson) {
            return Mage::helper('core')->jsonEncode($result);
        }
        return $result;
    }

    public function getCategoryCollection() {
        $collection = $this->_getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name', 'is_active'))
                ->setLoadProductCount(true)
            ;
            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    protected function _getNodesArray($node) {
        $result = array(
            'id'             => (int)$node->getId(),
            'parent_id'      => (int)$node->getParentId(),
            'children_count' => (int)$node->getChildrenCount(),
            'is_active'      => (bool)$node->getIsActive(),
            'disabled'     => ($node->getLevel() <= 1 || in_array(
                                    $node->getId(),
                                    $this->helper('evogue_catalogevent/adminhtml_event')->getInEventCategoryIds()
                                )),
            'name'           => $node->getName(),
            'level'          => (int)$node->getLevel(),
            'product_count'  => (int)$node->getProductCount(),
        );
        if ($node->hasChildren()) {
            $result['children'] = array();
            foreach ($node->getChildren() as $childNode) {
                $result['children'][] = $this->_getNodesArray($childNode);
            }
        }
        $result['cls'] = ($result['is_active'] ? '' : 'no-') . 'active-category';
        if ($result['disabled']) {
            $result['cls'] .= ' em';
        }
        $result['expanded'] = false;
        if (!empty($result['children'])) {
            $result['expanded'] = true;
        }
        return $result;
    }

    public function getLoadTreeUrl() {
        return $this->getUrl('*/*/categoriesJson');
    }
}
