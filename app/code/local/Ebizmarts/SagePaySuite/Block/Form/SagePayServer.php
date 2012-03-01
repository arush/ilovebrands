<?php

/**
 * Server payment form
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Form_SagePayServer extends Ebizmarts_SagePaySuite_Block_Form_SagePayToken
{

    protected function _prepareLayout()
    {
    	if(!$this->helper('sagepaysuite')->creatingAdminOrder()){
        	$this->setChild('token.cards.li', $this->getLayout()->createBlock('sagepaysuite/form_tokenList', 'token.cards.li')->setCanUseToken($this->canUseToken())->setPaymentMethodCode('sagepayserver'));
    	}
        return parent::_prepareLayout();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sagepaysuite/payment/form/sagePayServer.phtml');
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

}