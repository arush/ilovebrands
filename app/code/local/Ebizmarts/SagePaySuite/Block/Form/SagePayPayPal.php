<?php

/**
 * SagePay PayPal payment form
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Form_SagePayPayPal extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        parent::_construct();
       	$this->setTemplate('sagepaysuite/payment/form/sagePayPayPal.phtml');
    }

}