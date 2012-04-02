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
class TBT_Rewardsinstore_Model_Mysql4_Storefront_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $didSelectWebsiteName = false;
    
    protected function _construct ()
    {
        $this->_init('rewardsinstore/storefront');
    }
    
    /**
     * Performs a join to include the website's name in the result set fields, under the provided alias
     * @param string $alias The alias/fieldname under which to place the website name
     */
    public function selectWebsiteName($alias)
    {
        if( !$this->didSelectWebsiteName ) {
            $table_name = Mage::getModel('core/website')->getResource()->getMainTable();
            $this->getSelect()->joinLeft( $table_name, "{$table_name}.website_id = main_table.website_id", array( $alias => 'name' ) );
            $this->didSelectWebsiteName = true;
        }
        
        return $this;
    }
    
    /**
     * Filters the collection on a string of comma separated storefront ids.
     *
     * @param string $ids comma separated ids
     * @return $this
     */
    public function filterOnIds($ids)
    {
        $this->addFieldToFilter('storefront_id', array('IN' => explode(',', $ids)));
        return $this;
    }
    
    public function checkIfValueExists($attribute, $value)
    {
        foreach ($this as $storefront) {
            if ($storefront->getData($attribute) == $value) {
                return true;
            }
        }
        
        return false;
    }
}
