<?php
class Evogue_PageCache_Model_Mysql4_Crawler extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
         $this->_init('core/url_rewrite', 'url_rewrite_id');
    }

    public function getUrlStmt($storeId) {
        $table = $this->getTable('core/url_rewrite');
        $select = $this->_getReadAdapter()->select()
            ->from($table, array('store_id', 'request_path'))
            ->where('store_id=?', $storeId)
            ->where('is_system=1');
        return $this->_getReadAdapter()->query($select);
    }
}
