<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Refined block which shows currently applied filter values
 * @author Mana Team
 */
class Mana_Filters_Block_State extends Mage_Catalog_Block_Layer_State {
	public function getClearUrl() {
        return Mage::helper('mana_filters')->getClearUrl();
	}
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/filters/state.phtml');
    }
    public function getValueHtml($item) {
        $result = new Varien_Object();
        $block = $this;
        Mage::dispatchEvent('m_filter_value_html', compact('block', 'item', 'result'));
        return $result->getHtml() ? $result->getHtml() : '';
    }
}