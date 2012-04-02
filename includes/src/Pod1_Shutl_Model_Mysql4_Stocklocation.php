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
 * Shutl Stock Location Resource Model
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
class Pod1_Shutl_Model_Mysql4_StockLocation extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {   
        $this->_init('shutl/shutl_stocklocation', 'shutl_stocklocation_id');
    }
    
    
    public function uploadAndImport(Varien_Object $object) {

		
        if (!isset($_FILES['groups'])) { return false; }



        $csvFile = $_FILES['groups']['tmp_name']['shutl']['fields']['stocklocation']['value'];

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('shutl/shutl_stocklocation');


            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $headings = array_shift($csvLines);
               
				foreach($csvLines as $csvLine) {
					$values = $this->_getCsvValues($csvLine);
					if (strlen(trim($csvLine)) == 0) { continue; }
					if (sizeof($values) < 2) {
	                    $exceptions[0] = Mage::helper('shutl')->__('Invalid File Format');									
	                    break;
					}
					$location = Mage::getModel('shutl/stocklocation')->getCollection()->addFieldToFilter('shutl_reference', $values[0])->getFirstItem();
					$location->setShutlReference($values[0]);
					$location->setPostcode($values[1]);					
					$location->save();
					
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