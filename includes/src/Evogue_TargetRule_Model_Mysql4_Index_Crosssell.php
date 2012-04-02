<?php
class Evogue_TargetRule_Model_Mysql4_Index_Crosssell extends Evogue_TargetRule_Model_Mysql4_Index_Abstract {

    protected $_listType    = Evogue_TargetRule_Model_Rule::CROSS_SELLS;

    protected function _construct() {
        $this->_init('evogue_targetrule/index_crosssell', 'entity_id');
    }
}
