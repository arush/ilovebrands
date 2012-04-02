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
       	$this->setTemplate('sagepaysuite/payment/form/sagePayPayPal.phtml')
       		->setMethodTitle('') // Output PayPal mark, omit title
            ->setMethodLabelAfterHtml($this->getMarkTitle());
    }

    public function getMarkTitle()
    {
    	$locale = Mage::app()->getLocale()->getLocaleCode();

    	return '<img src="https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale='.$locale.'" alt="'.$this->helper('sagepaysuite')->__('PayPal').'" class="v-middle" />&nbsp;
<a href="https://www.paypal.com/uk/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside" onclick="javascript:window.open(\'https://www.paypal.com/uk/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside\',\'olcsagepaywhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, ,left=0, top=0, width=400, height=350\'); return false;">'.$this->helper('sagepaysuite')->__('What is PayPal?').'</a>';
    }

}