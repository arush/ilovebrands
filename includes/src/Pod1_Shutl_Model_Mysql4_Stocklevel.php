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
 * Shutl Stock level Resource Model
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
class Pod1_Shutl_Model_Mysql4_Stocklevel extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {   
        $this->_init('shutl/shutl_stocklevel', 'shutl_stocklevel_id');
    }
    
    public function uploadAndImport(Varien_Object $object) {

		$_helper = Mage::helper('shutl');
		$return = array();		

        if (!isset($_FILES['groups'])) { return false; }

        $csvFile = $_FILES['groups']['tmp_name']['shutl']['fields']['stocklevel']['value'];

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('shutl/shutl_stocklevel');


            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $headings = array_shift($csvLines);
				foreach($csvLines as $csvLine) {
					$values = $this->_getCsvValues($csvLine);
					if (strlen(trim($csvLine)) == 0) { continue; }
					if (sizeof($values) < 3) {
	                    $exceptions[] = Mage::helper('shutl')->__('Invalid File Format');									
	                    break;
					}
					$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $values[0]);
					if (!$product || !$product->getId()) {
	                    $exceptions[] = Mage::helper('shutl')->__('Skipping %s, invalid Sku.', $values[0]);
	                    continue;
					}
					$stockLocation = Mage::getModel('shutl/stocklocation')->getCollection()->addFieldToFilter('shutl_reference', $values[1])->getFirstItem();
					$stockLocationId = $stockLocation->getId();
					if (!$stockLocationId) {
	                    $exceptions[] = Mage::helper('shutl')->__('Skipping %s, invalid Shutl Reference.', $values[1]);
	                    continue;
					}

					$stockLevel = Mage::getModel('shutl/stocklevel')->getCollection()
						->addFieldToFilter('sku', $values[0])
						->addFieldToFilter('shutl_stocklocation_id', $stockLocationId)					
						->getFirstItem();
					$stockLevel->setSku($values[0]);
					$stockLevel->setShutlStocklocationId($stockLocationId);
					$stockLevel->setStockLevel($values[2]);
					$stockLevel->save();
					
				}				

                if (!empty($exceptions)) {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
        return $this;
    }

    protected function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

}