<?php

/**
 * Feed updates model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_Feed_Updates extends Ebizmarts_SagePaySuite_Model_Feed_Abstract {

	/**
	* Retrieve feed url
	*
	* @return string
	*/
	public function getFeedUrl() {
		return $this->getConfig('updates_url');
	}

	/**
	 * Checks feed
	 * @return
	 */
	public function check() {

		//Disable for now
		return $this;

		if(false === Mage::getStoreConfigFlag('payment/sagepaysuite/sagepay_notifications')){
			return $this;
		}

		if ((time() - Mage :: app()->loadCache('sagepaysuite_notifications_updates_lastcheck')) > $this->getConfig('check_frequency')) {
			$this->_getUpdates();
		}
	}

	protected function _getUpdates() {
		$feedData = array ();

		try {

			$node = $this->getFeedData();
			if (!$node) {
				return false;
			}

			foreach ($node->children() as $item) {

				$feedData[] = array (
					'severity' => (string) $item->severity,
					'date_added' => (string) $item->date_added,
					'title' => (string) $item->title,
					'description' => (string) $item->description,
					'url' => (string) $item->url,

				);

			}

			if ($feedData) {
				Mage :: getModel('adminnotification/inbox')->parse($feedData);
			}

			Mage :: app()->saveCache(time(), 'sagepaysuite_updates_feed_lastcheck');

			return true;

		} catch (Exception $e) {
			Ebizmarts_SagePaySuite_Log :: we($e);
			return false;
		}
	}

}