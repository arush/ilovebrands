<?php
class Evogue_PageCache_Model_Container_Customer extends Evogue_PageCache_Model_Container_Abstract {
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null) {
        $lifetime = Mage::getConfig()->getNode(Mage_Core_Model_Session_Abstract::XML_PATH_COOKIE_LIFETIME);
        return parent::_saveCache($data, $id, $tags, $lifetime);
    }
}
