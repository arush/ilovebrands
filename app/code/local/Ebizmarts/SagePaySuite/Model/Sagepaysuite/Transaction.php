<?php

class Ebizmarts_SagePaySuite_Model_Sagepaysuite_Transaction extends Mage_Core_Model_Abstract
{
	 /** Order instance
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
	protected $_paypal_trn = null;


    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sagepaysuite2/sagepaysuite_transaction');
    }

    public function loadByParent($orderId)
    {
        $this->load($orderId, 'order_id');
        return $this;
    }

    public function loadByVpsTxId($vpsTxId)
    {
        $this->load(trim($vpsTxId), 'vps_tx_id');
        return $this;
    }

    public function loadByVendorTxCode($vendorTxCode)
    {
        $this->load(trim($vendorTxCode), 'vendor_tx_code');
        return $this;
    }

	public function loadMultipleBy($attribute, $value)
	{
		$this->getCollection()
		->addFieldToFilter($attribute, $value);
		return $this;
	}

	/**
	 * Adds some API data to transaction
	 *
	 * @param int Order ID
	 */
	public function addApiDetails($orderId)
	{
		$this->loadByParent($orderId);

		if($this->getId()){

			try{
				$details = Mage::getModel('sagepayreporting/sagepayreporting')
						->getTransactionDetails(null, $this->getVpsTxId());

                if((string)$details->getErrorcode() === '0000'){
					$this->setEci($details->getEci())
						->setPaymentSystemDetails($details->getPaymentsystemdetails())
						->save();
				}

			}catch(Exception $e){}

		}
	}

	public function getisPayPalTransaction()
	{
		if(!is_null($this->_paypal_trn)){
			return $this->_paypal_trn;
		}

		$pp = Mage::getModel('sagepaysuite2/sagepaysuite_paypaltransaction')
		->loadByVendorTxCode($this->getVendorTxCode());

		if($pp->getId()){
			$this->_paypal_trn = true;
			$this->setPayPalData($pp);
		}else{
			$this->_paypal_trn = false;
		}
		return $this->_paypal_trn;
	}

	public function getCardExpiryDate()
	{
		$data = $this->getData('card_expiry_date');
		if(!$data){
			return null;
		}
		return $data[0].$data[1].'/'.$data[2].$data[3];
	}

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
    	$quote = Mage::getModel('sagepaysuite/api_payment')->getQuote();
    	$dbQuote = Mage::getModel('sagepaysuite/api_payment')->loadQuote($quote->getId(), Mage::app()->getStore()->getId());

        if($quote->getId() && $dbQuote->getId()){
    		$this->setQuoteId($quote->getId())
    		->setStoreId(Mage::app()->getStore()->getId());
        }

        return parent::_beforeSave();
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
    	/**
    	 * Multishipping parent TRN
    	 */
        if($this->getOrderId() && $this->getParentTrnId()){
        	$parent = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
        			  ->load($this->getParentTrnId())->toArray();
        	unset($parent['id']);
        	unset($parent['order_id']);
        	unset($parent['parent_trn_id']);

        	$this->addData($parent);
        }
    	/**
    	 * Multishipping parent TRN
    	 */

		/**
		 * Paypal
		 */
		$this->getisPayPalTransaction();

        return parent::_afterLoad();
    }

}
