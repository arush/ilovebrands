<?php
require_once  Mage::getModuleDir('controllers', 'Mage_Checkout').DS.'CartController.php';
class Idev_OneStepCheckout_CartController extends Mage_Checkout_CartController
{
    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack()
    {

        if ($returnUrl = $this->getRequest()->getParam('return_url')) {
            // clear layout messages in case of external url redirect
            if ($this->_isUrlInternal($returnUrl)) {
                $this->_getSession()->getMessages(true);
            }
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            //if config enabled, clear messages and redirect to checkout
            if(Mage::getStoreConfig('onestepcheckout/direct_checkout/redirect_to_cart')){

                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $allowedGroups = Mage::getStoreConfig('onestepcheckout/direct_checkout/group_ids');

                if(!empty($allowedGroups)){
                    $allowedGroups = explode(',',$allowedGroups);
                } else {
                    $allowedGroups = array();
                }

                if(!in_array($customerGroupId, $allowedGroups)){

                    $this->_getSession()->getMessages(true);
                    $this->_redirect('onestepcheckout', array('_secure'=>true));
                } else {
                    $this->_redirect('checkout/cart');
                }

            } else {
                $this->_redirect('checkout/cart');
            }


        }
        return $this;
    }

}
