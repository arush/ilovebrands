<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Encapsulates extensibility points for Mana_Filters module. Mana_Filters module internals call this helper to 
 * invoke this module extensions. Extension mechanisms include registration of additional functionality via 
 * config.xml, subscribing to events provided by this module, etc. 
 * @author Mana Team
 */
class Mana_Filters_Helper_Extended extends Mage_Core_Helper_Abstract {
	/**
	 * Provides phtml template file name to be used with this filter. Checks global configuration specific for 
	 * specified filter block
	 * @param Mage_Catalog_Block_Layer_Filter_Abstract $filterBlock Block to be rendered in returned template
	 * @return string
	 */
	public function getFilterTemplate($filterBlock) {
		
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		$type = $helper->getBlockType($filterBlock);
		if (/* @var $displayOptions Mage_Core_Model_Config_Element */ $displayOptions = Mage::getConfig()
			->getNode('mana_filters/display/'.$type)) 
		{
			$position = 0;
			/* @var $default Mage_Core_Model_Config_Element */ $default = null;
			foreach ($displayOptions->children() as $name => /* @var $options Mage_Core_Model_Config_Element */ $options) {
				if (!$position || $position > (string)$options->position) {
					$position = (string)$options->position;
					$default = $options;	
				}
			}
			if ($default) return (string)$default->template;
		}
		throw new Mage_Core_Exception($this->__('Filters of type "%s" can not be displayed - no template installed.'));
	}
	
	/**
	 * Modifies filter items and filter model itself as specified by extensions subscribed to 
	 * mana_filters_process_items event.
	 * @param Mage_Catalog_Model_Layer_Filter_Abstract $filter
	 * @param array $items
	 */
	public function processFilterItems($filter, $items) {
		$wrappedItems = new Varien_Object;
		$wrappedItems->setItems($items);
		Mage::dispatchEvent('mana_filters_process_items', array('filter' => $filter, 'items' => $wrappedItems));
		return $wrappedItems->getItems();
	}
}