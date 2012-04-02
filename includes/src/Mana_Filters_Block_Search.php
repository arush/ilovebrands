<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Block type for showing filters in search pages.
 * @author Mana Team
 * Injected into layout instead of standard catalogsearch/layer in layout XML file.
 */
class Mana_Filters_Block_Search extends Mage_CatalogSearch_Block_Layer {
	protected $_mode = 'search';
	
    /**
     * This method is called during page rendering to generate additional child blocks for this block.
     * @return Mana_Filters_Block_View_Category
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     * @see app/code/core/Mage/Catalog/Block/Layer/Mage_Catalog_Block_Layer_View::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock('mana_filters/state')
            ->setLayer($this->getLayer());
        $this->setChild('layer_state', $stateBlock);
            
        foreach (Mage::helper('mana_filters')->getFilterOptionsCollection() as $filterOptions) {
        	$displayOptions = $filterOptions->getDisplayOptions();
        	$block = $this->getLayout()->createBlock((string)$displayOptions->block, '', array(
        	    'filter_options' => $filterOptions,
        	    'display_options' => $displayOptions,
        	))->setLayer($this->getLayer());
            if ($attribute = $filterOptions->getAttribute()) {
        		$block->setAttributeModel($attribute);
            }
            $block->setMode($this->_mode)->init();
            $this->setChild($filterOptions->getCode() . '_filter', $block);
        }

        $this->getLayer()->apply();

        return $this;
    }
    
    public function getFilters() {
        $filters = array();
    	foreach (Mage::helper('mana_filters')->getFilterOptionsCollection() as $filterOptions) {
    		if ($filterOptions->getIsEnabledInSearch()) {
            	$filters[] = $this->getChild($filterOptions->getCode() . '_filter');
    		}
        }
        return $filters;
    }
}