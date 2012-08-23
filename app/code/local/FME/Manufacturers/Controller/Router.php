<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Manufacturers_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {    		
        $front = $observer->getEvent()->getFront();
        $router = new FME_Manufacturers_Controller_Router();
        $front->addRouter('manufacturers', $router);
        
    }

    public function match(Zend_Controller_Request_Http $request)
    {
		
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
		
        
        $route = Mage::helper('manufacturers')->getListIdentifier();		
       	$identifier = trim($request->getPathInfo(), '/');		
        $identifier = str_replace(Mage::helper('manufacturers')->getSeoUrlSuffix(), '', $identifier);
		$_SESSION["manufactureridentifier"] = $identifier;
		            
        if ( $identifier == $route ) {
        	$request->setModuleName('manufacturers')
        			->setControllerName('index')
        			->setActionName('index');
        			
        	return true;
        			
        }  elseif ($identifier != $route) {
			
			$manufacturerId = Mage::getModel('manufacturers/manufacturers')->checkIdentifier($identifier,Mage::app()->getStore()->getId());
        	if ( !$manufacturerId ) {
            	return false;
        	}
			$request->setModuleName('manufacturers')
            		->setControllerName('view')
            		->setActionName('index')
            		->setParam('id', $manufacturerId);
            		
			$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$identifier
			);
			
			return true;
			
		} elseif ( strpos($identifier, $route) === 0 && strlen($identifier) > strlen($route)) {
        	$identifier = trim(substr($identifier, strlen($route)), '/');  			
			$manufacturerId = Mage::getModel('manufacturers/manufacturers')->checkIdentifier($identifier,Mage::app()->getStore()->getId());
        	if ( !$manufacturerId ) {
            	return false;
        	}
        	$request->setModuleName('manufacturers')
            		->setControllerName('view')
            		->setActionName('index')
            		->setParam('id', $manufacturerId);
            		
			$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$identifier
			);
			return true;
        }  
       
        return false;

    }
}