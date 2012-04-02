<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Sort extends Mana_Core_Model_Source_Abstract {
    public function bySelected($a, $b) {
        if (!$a['m_selected'] && $b['m_selected']) return 1;
        if ($a['m_selected'] && !$b['m_selected']) return -1;
        return 0;
    }
    public function byName($a, $b) {
        if ($a['label'] < $b['label']) return -1;
        if ($a['label'] > $b['label']) return 1;
        return 0;
    }
    public function bySelectedName($a, $b) {
        $result = $this->bySelected($a, $b);
        return $result != 0 ? $result : $this->byName($a, $b);
    }
    public function byCount($a, $b) {
        if ($a['count'] < $b['count']) return 1;
        if ($a['count'] > $b['count']) return -1;
        return 0;
    }
    public function bySelectedCount($a, $b) {
        $result = $this->bySelected($a, $b);
        return $result != 0 ? $result : $this->byCount($a, $b);
    }
    protected function _getAllOptions() {
        return array(
            array('value' => '', 'label' => Mage::helper('mana_filters')->__('Position')),
            array('value' => 'bySelected', 'label' => Mage::helper('mana_filters')->__('Position (selected at the top)')),
            array('value' => 'byName', 'label' => Mage::helper('mana_filters')->__('Name')),
            array('value' => 'bySelectedName', 'label' => Mage::helper('mana_filters')->__('Name (selected at the top)')),
            array('value' => 'byCount', 'label' => Mage::helper('mana_filters')->__('Count')),
            array('value' => 'bySelectedCount', 'label' => Mage::helper('mana_filters')->__('Count (selected at the top)')),
        );
    }
}