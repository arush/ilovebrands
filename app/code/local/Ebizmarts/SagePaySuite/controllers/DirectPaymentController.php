<?php


/**
 * DIRECT payment controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_DirectPaymentController extends Mage_Core_Controller_Front_Action {
	protected function _expireAjax() {
		if (!Mage :: getSingleton('checkout/session')->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}

	public function preDispatch() {
		$storeId = $this->getRequest()->getParam('storeid');
		if ($storeId) {
			Mage :: app()->setCurrentStore((int) $storeId);
		}

		parent :: preDispatch();
		return $this;
	}

	public function saveOrderAction() {
		$this->_expireAjax();

		$resultData = array ();

		try {
			$result = $this->getDirectModel()->registerTransaction($this->getRequest()->getPost());
			$resultData = $result->getData();

			$response_status = $result->getResponseStatus();

			if($response_status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_PAYPAL_REDIRECT){

				$resultData = array (
										'success' => 'true',
										'response_status' => 'paypal_redirect',
										'redirect' => $result->getData('pay_pal_redirect_url')
									);

			}else if ($response_status == Ebizmarts_SagePaySuite_Model_Api_Payment :: RESPONSE_CODE_3DAUTH) {
				$resultData = array (
					'success' => 'true',
					'response_status' => 'threed',
					'redirect' => Mage :: getModel('core/url'
				)->getUrl('sgps/DirectPayment/threedPost', array('_secure' => true)));
			} else
				if ($response_status == Ebizmarts_SagePaySuite_Model_Api_Payment :: RESPONSE_CODE_APPROVED || $response_status == Ebizmarts_SagePaySuite_Model_Api_Payment :: RESPONSE_CODE_REGISTERED) {

					$op = Mage :: getSingleton('checkout/type_onepage');
					$op->getQuote()->collectTotals();
					$op->saveOrder();

					$resultData = array (
						'success' => 'true',
						'response_status' => 'OK'
					);
					Mage::helper('sagepaysuite')->deleteQuote();
				}
		} catch (Exception $e) {
			Sage_Log :: logException($e);
			$resultData['response_status'] = 'ERROR';
			$resultData['response_status_detail'] = $e->getMessage();
		}

		return $this->getResponse()->setBody(Zend_Json :: encode($resultData));
	}

	/**
	 * Return all customer cards list for onepagecheckout use.
	 */
	public function getTokenCardsHtmlAction() {
		$html = '';

		try {
			$html .= $this->getLayout()->createBlock('sagepaysuite/form_tokenList', 'token.cards.li')->setCanUseToken(true)->setPaymentMethodCode('sagepaydirectpro')->toHtml();
		} catch (Exception $e) {
			Ebizmarts_SagePaySuite_Log :: we($e);
		}

		return $this->getResponse()->setBody(str_replace(array (
			'<div id="tokencards-payment-sagepaydirectpro">',
			'</div>'
		), array (), $html));
	}

	public function getDirectModel() {
		return Mage :: getModel('sagepaysuite/sagePayDirectPro');
	}

	public function registerTokenAction() {
		$resultData = array ();
		try {
			$resultData = $this->getDirectModel()->registerTransaction($this->getRequest()->getPost(), true);
		} catch (Exception $e) {
			Ebizmarts_SagePaySuite_Log :: we($e);
			$resultData['success'] = 'false';
			$resultData['response_status'] = 'ERROR';
			$resultData['response_status_detail'] = $e->getMessage();
		}

		return $this->getResponse()->setBody(Zend_Json :: encode($resultData));
	}

	protected function _getThreedPostHtml() {
		return $this->getLayout()->createBlock('sagepaysuite/checkout_threedredirectpost')->toHtml();
	}

	public function threedPostAction() {
		$this->getResponse()->setBody($this->_getThreedPostHtml());
	}

	public function errorAction() {
		$this->_redirect('checkout/onepage');
	}

	/**
	 * When a customer cancel payment from paypal.
	 */
	public function cancelAction() {
		$session = Mage :: getSingleton('checkout/session');
		$session->setQuoteId($session->getPaypalStandardQuoteId(true));

		$this->_redirect('checkout/cart');
	}

	public function callback3dAction() {

		header('Content-type: text/html');

		$image = Mage :: helper('sagepaysuite')->getIndicator();

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"><html><head></head><body>
					<div style="background-image:url(' . $image . '); background-position: center center;background-repeat: no-repeat;height: 400px;">&nbsp;</div>';

		$error = false;
		try {

			Mage :: getModel('sagepaysuite/sagePayDirectPro')->saveOrderAfter3dSecure($this->getRequest()->getPost('PaRes'), Mage :: getSingleton('sagepaysuite/session')->getEmede());
		} catch (Exception $e) {

			Mage :: getSingleton('sagepaysuite/session')->setAcsurl(null)->setPareq(null)->setSageOrderId(null)->setSecure3d(null)->setEmede(null)->setPares(null)->setMd(null);

			Ebizmarts_SagePaySuite_Log :: we($e);

			$error = true;
			$message = $e->getMessage();

			echo '<script type="text/javascript">window.parent.notifyThreedError("' . $message . '");</script></body>
			  </html>';
			die;
		}

		if (!$error) {

			/**
			 * set the quote as inactive after back from paypal
			 */
			Mage :: getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

			/**
			 * send confirmation email to customer
			 */
			$order = Mage :: getModel('sales/order');

			$order->load(Mage :: getSingleton('checkout/session')->getLastOrderId());
			if ($order->getId() && (Mage :: helper('sagepaysuite')->isLocalhost() === false)) {
				$order->sendNewOrderEmail();
			}

			$this->getResponse()->setBody('<script type="text/javascript">window.parent.location.href="' . Mage :: getUrl('checkout/onepage/success', array (
				'_secure' => true
			)) . '";</script></body></html>');

		}
	}

}