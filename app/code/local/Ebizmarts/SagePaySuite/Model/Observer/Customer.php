<?php

/**
 * Customer events observer model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Model_Observer_Customer
{
    /**
     * Set cards for customer on CHECKOUT REGISTER
     */
    public function addRegisterCheckoutTokenCards($e)
    {
        $customer = $e->getEvent()->getCustomer();

        $vdata = Mage::getSingleton('core/session')->getVisitorData();

        $sessionCards = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->getCollection()
                ->addFieldToFilter('visitor_session_id', (string)$vdata['session_id']);

        if($sessionCards->getSize() > 0){
            foreach($sessionCards as $_c){
                if($_c->getCustomerId() == 0){
                    $_c->setCustomerId($customer->getId())
                            ->setVisitorSessionId(null)
                        ->save();
                }
            }
        }
    }
}