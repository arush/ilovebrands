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
 * Shutl Export Controller
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */

class Pod1_Shutl_ExportController extends Mage_Adminhtml_Controller_Action {

	public function stocklocationAction() {

        $collection = Mage::getModel('shutl/stocklocation')->getCollection();

        $csv = '';


        $csvHeader = array('"'.Mage::helper('adminhtml')->__('Shutl Reference').'"', '"'.Mage::helper('adminhtml')->__('Postcode').'"');
        $csv .= implode(',', $csvHeader)."\n";

        foreach ($collection as $item) {
            $csvData = array($item->getData('shutl_reference'), $item->getData('postcode'));
            foreach ($csvData as $cell) {
                $cell = '"'.str_replace('"', '""', $cell).'"';
            }
            $csv .= implode(',', $csvData)."\n";
        }

        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=shutl_stocklocation.csv");
        echo $csv;
        exit;
    }
    
    
	public function stocklevelAction() {

        $collection = Mage::getModel('shutl/stocklevel')->getCollection();

        $csv = '';


        $csvHeader = array('"'.Mage::helper('adminhtml')->__('SKU').'"', '"'.Mage::helper('adminhtml')->__('Shutl Store Reference').'"', '"'.Mage::helper('adminhtml')->__('Stock Level').'"');
        $csv .= implode(',', $csvHeader)."\n";

        foreach ($collection as $item) {
        	$stockLocation = Mage::getModel('shutl/stocklocation')->load($item->getData('shutl_stocklocation_id'));
        
            $csvData = array($item->getData('sku'), $stockLocation->getShutlReference(), $item->getData('stock_level'));
            foreach ($csvData as $cell) {
                $cell = '"'.str_replace('"', '""', $cell).'"';
            }
            $csv .= implode(',', $csvData)."\n";
        }

        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=shutl_stocklevel.csv");
        echo $csv;
        exit;
    }
    
}