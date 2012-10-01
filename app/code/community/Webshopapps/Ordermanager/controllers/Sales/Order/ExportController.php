<?php
/**
 * Magento Webshopapps Order Export Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Webshopapps
 * @package    Webshopapps_OrderExport
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Genevieve Eddison, Jonathan Feist, Farai Kanyepi <sales@webshopapps.com>
 * */
class Webshopapps_Ordermanager_Sales_Order_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Exports orders defined by id in post param "order_ids" to csv and offers file directly for download
     * when finished.
     */
    public function csvexportAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());
    	
		switch(Mage::getStoreConfig('order_export/export_orders/output_type')){
			case 'Standard':
				$file = Mage::getModel('webshopapps_ordermanager/export_csv')->exportOrders($orders);
				$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
				break;
			case 'Sage':
				$file = Mage::getModel('webshopapps_ordermanager/export_sage')->exportOrders($orders);
				$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file)); 
				break;
			case 'Highrise':
				$failedList = '';
    			$successCount = 0;
    			$failCount = 0;
            	try {
                	$results = Mage::getModel('webshopapps_ordermanager/export_highrise')->exportOrders($orders);
	            	foreach ($results as $orderid => $status) {
	    				if ($status > 0 ) $successCount++;
	    				else {
	    					$failedList.= $orderid .' ';
	    					$failCount++;
	    				}
	    			}
            	}
            	catch (Exception $e) {
            		Mage::log($e->getMessage());
            		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sales')->__($e->getMessage()));
            	}

	        	if ($failCount > 0) {
	        		$failedString = $successCount .' order(s) have been imported. The following orders failed to import: ' .$failedList;
	        		$this->_getSession()->addError($this->__( $failedString));
	        		
	        	}
	        	else {
	            	$this->_getSession()->addSuccess($this->__('%s order(s) have been imported', $successCount));
	        	}
	        	$this->_redirect('*/sales_order');
				//$this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
				break;		
		}
    }
}
?>