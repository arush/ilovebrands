<?php

/**
 * 3D Redirect post
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Block_Checkout_Threedredirectpost extends Mage_Core_Block_Template {

	protected function _getSageSession() {
		return Mage :: getModel('sagepaysuite/api_payment')->getSageSuiteSession();
	}

	protected function _toHtml() {
		try {

			$form = new Varien_Data_Form;
			$form->setAction($this->_getSageSession()->getAcsurl())->setId('sagepaydirectpro_3dsecure')->setName('sagepaydirectpro_3dsecure')->setMethod('POST')->setUseContainer(true);

			$params = array (
				'_secure' => true,
			'storeid' => Mage :: app()->getStore()->getId(),);

			$_shipSessData = Mage :: getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->getShippingMethod();
			if ($_shipSessData) {
				$params['shipmethod'] = $_shipSessData;
			}

			$postUrl = Mage :: getModel('core/url')->addSessionParam()->getUrl('sgps/directPayment/callback3d', $params);

			$form->addField('PaReq', 'hidden', array (
				'name' => 'PaReq',
			'value' => $this->_getSageSession()->getPareq()));
			$form->addField('MD', 'hidden', array (
				'name' => 'MD',
			'value' => $this->_getSageSession()->getEmede()));
			#$form->addField('TermUrl', 'hidden', array('name'=>'TermUrl', 'value' => Mage::getUrl('sgps/directPayment/callback3d', array('_secure' => true))));
			$form->addField('TermUrl', 'hidden', array (
				'name' => 'TermUrl',
				'value' => $postUrl
			));

			$html = '<html><body>';
			$html .= '<code>' . $this->__('Loading 3D secure form...') . '</code>';
			$html .= $form->toHtml();
			$html .= '<script type="text/javascript">document.getElementById("sagepaydirectpro_3dsecure").submit();</script>';
			$html .= '</body></html>';

			Sage_Log :: log($html, null, 'SagePaySuite_REQUEST.log');

		} catch (Exception $e) {
			Ebizmarts_SagePaySuite_Log :: we($e);
		}

		return $html;
	}
}