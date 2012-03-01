<?php
class Evogue_TargetRule_Model_Mysql4_Index_Related extends Evogue_TargetRule_Model_Mysql4_Index_Abstract {
    protected $_listType    = Evogue_TargetRule_Model_Rule::RELATED_PRODUCTS;

    protected function _construct() {
        $this->_init('evogue_targetrule/index_related', 'entity_id');
    }
}
