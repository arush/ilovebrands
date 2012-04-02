<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Cron extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Cron');
    }

    public function getDescription() {
        return $this->__('Check cron is running scheduled tasks.');
    }

    protected function generateSummary() {

        $now = Mage::getModel('testsweet/observer_crontest')->getCurrentTimestamp();;
        $timestamp = Mage::getModel('testsweet/observer_crontest')->getCronTimestamp();
        $isworking = Mage::getModel('testsweet/observer_crontest')->isWorking();

        if ($isworking) {
            $this->addPass($this->__("Cron timestamp: [%s], current timestamp: [%s].", $timestamp, $now));
        } else {
            $this->addFail($this->__("Cron might not be active. Crontest last timestamp: [%s], current system timestamp: [%s].", $timestamp, $now), $this->__("Check crontab has: */5 * * * * /bin/sh /[magento_path]/cron.sh"));
            $this->addNotice($this->__("If cache is enabled try this test again in 10 minutes while Magento cache is disabled."));
            $this->addNotice($this->__("More help can be found here: http://www.sweettoothrewards.com/wiki/index.php/Setting_up_CRON_Jobs_in_Magento") );
        }
    }

}
