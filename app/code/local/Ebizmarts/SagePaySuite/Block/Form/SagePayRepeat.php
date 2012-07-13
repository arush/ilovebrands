<?php

/**
 * Repeat payment form
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Form_SagePayRepeat extends Ebizmarts_SagePaySuite_Block_Form_SagePayToken
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sagepaysuite/payment/form/sagePayRepeat.phtml');
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

	public function getSearchGridUrl()
	{
		return $this->helper('adminhtml')->getUrl('sgpsSecure/adminhtml_repeatpayment/index');
	}

    /**
     * Create buttonn and return its html
     */
    public function getSearchButtonHtml() {
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->helper('sagepaysuite')->__('Search'),
                'onclick'   => 'repeatpaymentSearchGrid()',
                'class'     => '',
                'type'      => 'button',
                'id'        => 'sagepayrepeat-search',
            ))
            ->toHtml();
    }

}