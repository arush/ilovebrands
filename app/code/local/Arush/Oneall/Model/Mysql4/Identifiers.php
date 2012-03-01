<?php

class Arush_Oneall_Model_Mysql4_Identifiers extends Mage_Core_Model_Mysql4_Abstract {

	protected function _construct() {
		$this->_init('oneall/identifiers', 'oneall_identifier_id');
	}

}