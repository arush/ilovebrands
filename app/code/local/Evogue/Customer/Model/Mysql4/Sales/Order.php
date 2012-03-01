<?php
class Evogue_Customer_Model_Mysql4_Sales_Order extends Evogue_Customer_Model_Mysql4_Sales_Abstract {

    protected function _construct() {
        $this->_init('evogue_customer/sales_order', 'entity_id');
    }
}
