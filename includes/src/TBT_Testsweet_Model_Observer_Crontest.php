<?php

class TBT_Testsweet_Model_Observer_Crontest {

    public function run() {
        $timestamp = $this->getCurrentTimestamp();
        Mage::getConfig()->saveConfig('testsweet/crontest/timestamp', $timestamp, 'default', 0);
    }
    
    public function getCurrentTimestamp() {
        $timestamp = (string)time();
        return $timestamp;
    }

    public function getCronTimestamp() {
        //$timestamp = (string)Mage::getConfig()->getNode('testsweet/crontest/timestamp', 'default', 0);
        $timestamp = Mage::getStoreConfig('testsweet/crontest/timestamp');
        return $timestamp;
    }

    public function isWorking() {
        $timestamp = $this->getCronTimestamp();
        if (empty($timestamp))
            return false;

        $seconds = $this->getCurrentTimestamp() - $timestamp;

        // if the timestamp is within 30 minuets return true
        return $seconds < (60 * 30);
    }

}
