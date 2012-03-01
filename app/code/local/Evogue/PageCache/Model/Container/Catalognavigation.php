<?php
class Evogue_PageCache_Model_Container_Catalognavigation extends Evogue_PageCache_Model_Container_Abstract {
    protected function _getBlockCacheId() {
        return $this->_placeholder->getAttribute('short_cache_id');
    }

    protected function _getCategoryCacheId() {
        $shortCacheId = $this->_placeholder->getAttribute('short_cache_id');
        $categoryPath = $this->_placeholder->getAttribute('category_path');
        if (!$shortCacheId || !$categoryPath) {
            return false;
        }
        return $shortCacheId . '_' . $categoryPath;
    }

    public function applyWithoutApp(&$content) {
        $blockCacheId = $this->_getBlockCacheId();
        $categoryCacheId = $this->_getCategoryCacheId();
        if ($blockCacheId && $categoryCacheId) {
            $blockContent = $this->_loadCache($blockCacheId);
            $categoryUniqueClasses = $this->_loadCache($categoryCacheId);
            if ($blockContent && $categoryUniqueClasses !== false) {
                if ($categoryUniqueClasses != '') {
                    $regexp = '';
                    foreach (explode(' ', $categoryUniqueClasses) as $categoryUniqueClass) {
                        $regexp .= ($regexp ? '|' : '') . preg_quote($categoryUniqueClass);
                    }
                    $blockContent = preg_replace('/(?<=\s|")(' . $regexp . ')(?=\s|")/u', '$1 active', $blockContent);
                }
                $this->_applyToContent($content, $blockContent);
                return true;
            }
        }
        return false;
    }

    public function saveCache($blockContent) {
        $blockCacheId = $this->_getBlockCacheId();
        if ($blockCacheId) {
            $categoryCacheId = $this->_getCategoryCacheId();
            if ($categoryCacheId) {
                $categoryUniqueClasses = '';
                $classes = array();
                $classesCount = preg_match_all('/(?<=\s)class="(.*?active.*?)"/u', $blockContent, $classes);
                for ($i = 0; $i < $classesCount; $i++) {
                    $classAttribute = $classes[0][$i];
                    $classValue = $classes[1][$i];
                    $classInactive = preg_replace('/\s+active|active\s+|active/', '', $classAttribute);
                    $blockContent = str_replace($classAttribute, $classInactive, $blockContent);
                    $matches = array();
                    if (preg_match('/(?<=\s|^)nav-.+?(?=\s|$)/', $classValue, $matches)) {
                        $categoryUniqueClasses .= ($categoryUniqueClasses ? ' ' : '') . $matches[0];
                    }
                }
                $this->_saveCache($categoryUniqueClasses, $categoryCacheId);
            }
            if (!Mage::app()->getCache()->test($blockCacheId)) {
                $this->_saveCache($blockContent, $blockCacheId);
            }
        }
        return $this;
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $categoryPath = $this->_placeholder->getAttribute('category_path');

        $block = new $block;
        $block->setTemplate($template);

        if ($categoryPath) {
            $categoryPath = explode('/', $categoryPath);
            $categoryId = end($categoryPath);
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }

        return $block->toHtml();
    }
}
