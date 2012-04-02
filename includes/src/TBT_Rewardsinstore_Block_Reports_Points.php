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
 * Displays any singular points values for the analytics page (totals, averages, etc.)
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Reports_Points extends Mage_Adminhtml_Block_Dashboard_Bar
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rewardsinstore/reports/pointbar.phtml');
    }
    
    protected function _prepareLayout()
    {
        // TODO: These should be grabbing params of storefront/website/group first, THEN passing in the result
        
        $totalPoints = Mage::helper('rewardsinstore/reports_points')->getTotalPointsDistributed($this->getRequest()->getParam('storefront'));
        $totalPoints = $totalPoints ? (int)$totalPoints : 0;
        $this->addTotal($this->__('Lifetime Points Awarded Instore'), Mage::helper('rewards')->getPointsString(array(1 => $totalPoints)), true);
        
        $avgPoints = Mage::helper('rewardsinstore/reports_points')->getAveragePointsPerOrder($this->getRequest()->getParam('storefront'));
        $avgPoints = $avgPoints ? (int)$avgPoints : 0;
        $this->addTotal($this->__('Average Points Per Instore Order'), Mage::helper('rewards')->getPointsString(array(1 => $avgPoints)), true);
    }
}
