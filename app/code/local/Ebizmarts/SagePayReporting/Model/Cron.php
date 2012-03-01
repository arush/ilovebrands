<?php

class Ebizmarts_SagePayReporting_Model_Cron
{

	public function getThirdmanScores($cron)
	{
		$tblName = Mage::getSingleton('core/resource')->getTableName('sagepayreporting_fraud');
		$sagepayOrders = Mage::getResourceModel('sales/order_grid_collection');

		$sagepayOrders->getSelect()
		->joinLeft(array (
			'pmnt' => $sagepayOrders->getTable('sales/order_payment'
		)),
		'main_table.entity_id = pmnt.parent_id', array ())
		->joinLeft(array (
			'sls' => $sagepayOrders->getTable('sales/order')
		),
		'main_table.entity_id = sls.entity_id', array ())
		->where("(pmnt.method = 'sagepaydirectpro' OR pmnt.method = 'sagepayserver' OR pmnt.method = 'sagepayserver_moto' OR pmnt.method = 'sagepaydirectpro_moto' OR pmnt.method = 'sagepayform' OR pmnt.method = 'sagepaypaypal') AND (main_table.entity_id NOT IN (SELECT order_id FROM ". $tblName ."))")
		->limit(10);

		foreach ($sagepayOrders as $_order) {

			$_order = Mage::getModel('sales/order')->load($_order->getId());
			$rs = Mage::getModel('sagepayreporting/sagepayreporting')->getTransactionDetails($_order->getSagepayInfo()->getVendorTxCode());

			if (!is_object($rs) OR $rs->getError()) {
				continue;
			}

			try {

				/**
				 *  Automatic fulfill
				 */
				if((int)$rs->getT3mscore() && (string)$rs->getT3maction() != 'NORESULT'){

					# Update Thirdman score on DB
					Mage::getModel('sagepayreporting/fraud')->updateThirdMan($_order->getId(), $rs);

					$dbtrn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->loadByVendorTxCode($_order->getSagepayInfo()->getVendorTxCode());

					$canAuthorise = ($dbtrn->getTxType() == 'AUTHENTICATE' && !$dbtrn->getAuthorised());
					$canRelease = ($dbtrn->getTxType() == 'DEFERRED' && !$dbtrn->getReleased());
					$rank = ( $this->_getCanRank() && ($this->_getRank() <= (int)$rs->getT3mscore()) );

					if(($canAuthorise || $canRelease) && $rank){
						Mage::getModel('sagepaysuite/api_payment')->invoiceOrder($_order->getId(), Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
					}

				}
				/**
				 *  Automatic fulfill
				 */

			} catch (Exception $e) {
				Sage_Log :: logException($e);
			}

		}

	}

	protected function _getCanRank()
	{
		return Mage::getStoreConfigFlag('payment/sagepaysuite/auto_fulfill_low_risk_trn');
	}

	protected function _getRank()
	{
		return (int)Mage::getStoreConfig('payment/sagepaysuite/auto_fulfill_low_risk_trn_value');
	}

}