<?php
class Evogue_CustomerBalance_Block_Adminhtml_Widget_Grid_Column_Renderer_Currency
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency {

    protected static $_websiteBaseCurrencyCodes = array();

    protected function _getCurrencyCode($row) {
        $websiteId = $row->getData('website_id');
        $orphanCurrency = $row->getData('base_currency_code');
        if ($orphanCurrency !== null) {
            return $orphanCurrency;
        }
        if (!isset(self::$_websiteBaseCurrencyCodes[$websiteId])) {
            self::$_websiteBaseCurrencyCodes[$websiteId] = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();
        }
        return self::$_websiteBaseCurrencyCodes[$websiteId];
    }

    protected function _getRate($row) {
        return 1;
    }
}
