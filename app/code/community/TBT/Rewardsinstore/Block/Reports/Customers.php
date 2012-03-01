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
 * Display all customer metrics related to Instore
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Reports_Customers extends Mage_Adminhtml_Block_Dashboard_Bar
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rewardsinstore/reports/breakdownbar.phtml');
    }
    
    protected function _prepareLayout()
    {
        // TODO: These need to be filtered by storefront
        
        $helper = Mage::helper('rewardsinstore/reports_customer');
        
        $count = $helper->getTotalInstoreParticipants();
        $label = ($count != 1) ?
            $this->__("total customers have been rewarded through Instore.") :
            $this->__("total customer has been rewarded through Instore.");
        $this->addTotal($label, $count, true);
        
        $count = $helper->getTotalInstoreCustomers();
        $label = $this->__("of those customer accounts were created from Instore.");
        $this->addTotal($label, $count, true);
        
        $count = $helper->getActiveInstoreCustomers();
        $label = ($count != 1) ?
            $this->__("Instore customers have logged into your online store.") :
            $this->__("Instore customer has logged into your online store.");
        $this->addTotal($label, $count, true);
        
        $count = $helper->getWebPurchasingInstoreCustomers();
        $label = ($count != 1) ? 
            $this->__("Instore customers have made a purchase online.") :
            $this->__("Instore customers has made a purchase online.");
        $this->addTotal($label, $count, true);
        
        $count = $helper->getRepeatInstoreCustomers();
        $label = ($count != 1) ?
            $this->__("customers are repeat Instore customers.") :
            $this->__("customer is a repeat Instore customer.");
        $this->addTotal($label, $count, true);
    }
    
    protected function getLabel()
    {
        return 'Customer Metrics';
    }
}
