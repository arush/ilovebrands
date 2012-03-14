<?php

class Ebizmarts_SagePaySuite_Model_Sagepaysuite_Paypaltransaction extends Mage_Core_Model_Abstract
{

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sagepaysuite2/sagepaysuite_paypaltransaction');
    }

    public function loadByParent($trnId)
    {
        $this->load($trnId, 'transaction_id');
        return $this;
    }

    public function loadByVendorTxCode($vendorTxCode)
    {
        $this->load($vendorTxCode, 'vendor_tx_code');
        return $this;
    }

}