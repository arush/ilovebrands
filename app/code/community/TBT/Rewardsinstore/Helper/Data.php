<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return true;
    }
    
    /**
     * This function formats a list of strings for displaying in a grid.
     * You can also limit the nubmer of elements that are printed, which
     * will add 'and x more' to the end of the text.
     *
     * @param array $array
     * @param string $delimeter
     * @param int $maxElementsShown number of elements to print in full. 0 for all.
     * @return string Text to be shown
     */
    public function arrayToString($array, $delimeter, $maxElementsShown = 0)
    {
        if ($maxElementsShown == 0) {
            return implode($delimeter, $array);
        }
        
        $text = implode($delimeter, array_slice($array, 0, $maxElementsShown)); 
        
        // Append 'and x more' if applicable
        $andNumOthers = count($array) - $maxElementsShown;
        if ($andNumOthers > 0) {
            // TODO: add the option to hover over 'and x more' to see a tooltip with full list
            $text .= $delimeter . sprintf(Mage::helper('rewardsinstore')->__('and %s more.'), $andNumOthers); 
        }
        
        return $text;
    }
    
    /**
     * Returns an array of ids in the collection.
     * Usually we can use $collection->getIds() but this can
     * be used for the collections that extend Varien_Data_Collection_Db
     * which doesn't support this funtion. 
     * Eg. Mage_Directory_Model_Mysql4_Region_Collection does this.
     *
     * @param unknown_type $collection
     * @return unknown
     */
    public function getCollectionIds($collection)
    {
        $ids = array();
        foreach ($collection as $item) {
            array_push($ids, $item->getId());
        }
        
        return $ids;
    }
    
    /**
     * Easy way to set a Magento core config value.
     *
     * @param unknown_type $key
     * @param unknown_type $value
     */
    public function setConfigValue($key, $value, $clearCache = true) 
    {
        Mage::getConfig()
            ->saveConfig($key, $value);
            
        if ($clearCache) {
            Mage::getConfig()->cleanCache();
        }
    }
    
    /**
     * Log to rewardsinstore file
     */
    public function log($msg)
    {
        Mage::log($msg, null, "rewardsinstore.log");
    }
    
    /**
     * Log to rewardsinstore.error file
     */
    public function logException($ex)
    {
        Mage::log("\n" . $ex->__toString(), Zend_Log::ERR, "rewardsinstore.error.log");
    }
}
