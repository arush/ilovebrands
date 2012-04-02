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
class TBT_Rewardsinstore_Helper_Directory extends Mage_Core_Helper_Abstract
{
    /**
     * This function identifies a region model in Magento
     * by matching it on either id, code, or name.
     * 
     * An region model with a null id is returned if no region is found.
     *
     * @param unknown_type $region_identifier ID, Code, or Name of region
     * @param unknown_type $country_id
     * @return Mage_Directory_Model_Region
     */
    public function getRegionInCountry($region_identifier, $country_id)
    {
        // Check for region id
        if (is_numeric($region_identifier)) {
            $new_region = Mage::getModel('directory/region')->load($region_identifier);
        } else {
            // Check for region code
            $new_region = Mage::getModel('directory/region')->loadByCode($region_identifier);
            
            // Finally, try to match on region name
            if (!$new_region->getId()) {
                $new_region = Mage::getModel('directory/region')->loadByName($region_identifier, $country_id);
            }
        }
        return $new_region;
    }
}
