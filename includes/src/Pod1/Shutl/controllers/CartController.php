<?php
/**
 * Pod1 Shutl extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @copyright  Copyright (c) 2010 Pod1 (http://www.pod1.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shutl Quote front controller
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
 
class Pod1_Shutl_CartController extends Mage_Core_Controller_Front_Action {

	public function updateAction() {
		$_helper	= Mage::helper('shutl');
        $method     = (string) $this->getRequest()->getParam('method');
        $date	    = $_helper->convertDate((string) $this->getRequest()->getParam('date'));
        $time	    = (string) $this->getRequest()->getParam('time');                        
 
        Mage::getSingleton('core/session')->setShutlDate($date);
        Mage::getSingleton('core/session')->setShutlRawDate(str_replace('-', '/', $this->getRequest()->getParam('date')));        
        
        Mage::getSingleton('core/session')->setShutlTime($time);
        Mage::getSingleton('core/session')->setShutlMethod($method);        
	
	}

    public function estimatePostAction()
    {
		$_helper	= Mage::helper('shutl');
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');
        $method     = (string) $this->getRequest()->getParam('method');
        $date	    = $_helper->convertDate((string) $this->getRequest()->getParam('date'));              
        $time	    = (string) $this->getRequest()->getParam('time');                        
        Mage::getSingleton('core/session')->setShutlDate($date);
        Mage::getSingleton('core/session')->setShutlRawDate(str_replace('-', '/', $this->getRequest()->getParam('date')));        
        Mage::getSingleton('core/session')->setShutlTime($time);
        Mage::getSingleton('core/session')->setShutlMethod($method);        
        Mage::getSingleton('core/session')->setShutlPostcode($postcode);        
        

        Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        Mage::getSingleton('checkout/cart')->getQuote()->save();

        $this->_goBack();
    }
    
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
            $this->_redirect('checkout/cart');
        }
        return $this;
    }    

}