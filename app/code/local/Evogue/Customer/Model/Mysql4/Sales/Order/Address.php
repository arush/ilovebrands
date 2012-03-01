<?php
class Evogue_Customer_Model_Mysql4_Sales_Order_Address extends Evogue_Customer_Model_Mysql4_Sales_Address_Abstract {

    protected function _construct() {
        $this->_init('evogue_customer/sales_order_address', 'entity_id');
    }
}
