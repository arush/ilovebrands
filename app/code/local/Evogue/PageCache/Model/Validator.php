<?php
class Evogue_PageCache_Model_Validator {

    protected $_dataChangeDependency = array(
        'Mage_Catalog_Model_Product',
        'Mage_Catalog_Model_Category',
        'Mage_Catalog_Model_Resource_Eav_Attribute',
        'Mage_Tag_Model_Tag',
        'Mage_Review_Model_Review',
        'Evogue_Cms_Model_Hierarchy_Node',
        'Evogue_Banner_Model_Banner',
    );
    protected $_dataDeleteDependency = array(
        'Mage_Catalog_Model_Category',
        'Mage_Catalog_Model_Resource_Eav_Attribute',
        'Mage_Tag_Model_Tag',
        'Mage_Review_Model_Review',
        'Evogue_Cms_Model_Hierarchy_Node',
        'Evogue_Banner_Model_Banner',
    );

    protected function _invalidateCache() {
        Mage::app()->getCacheInstance()->invalidateType('full_page');
    }

    protected function _getObjectClasses($object) {
        $classes = array();
        if (is_object($object)) {
            $classes[] = get_class($object);
            $parent = $object;
            while ($parentClass = get_parent_class($parent)) {
                $classes[] = $parentClass;
                $parent = $parentClass;
            }
        }
        return $classes;
    }

    public function checkDataChange($object) {
        $classes = $this->_getObjectClasses($object);
        $intersect = array_intersect($this->_dataChangeDependency, $classes);
        if (!empty($intersect)) {
            $this->_invalidateCache();
        }
        return $this;
    }

    public function checkDataDelete($object) {
        $classes = $this->_getObjectClasses($object);
        $intersect = array_intersect($this->_dataDeleteDependency, $classes);
        if (!empty($intersect)) {
            $this->_invalidateCache();
        }
        return $this;
    }
}
