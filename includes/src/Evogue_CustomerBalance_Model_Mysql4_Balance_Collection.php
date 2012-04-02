<?php
class Evogue_CustomerBalance_Model_Mysql4_Balance_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('evogue_customerbalance/balance');
    }

    public function addWebsitesFilter($websiteIds) {
        $this->getSelect()->where(
            $this->getConnection()->quoteInto('main_table.website_id IN (?)', $websiteIds)
        );
        return $this;
    }

    protected function _afterLoad() {
        parent::_afterLoad();
        $this->walk('afterLoad');
        return $this;
    }
}
