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
 * TODO: is this actually used?
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Reports_Totalsbar extends Mage_Adminhtml_Block_Dashboard_Abstract
{
    protected $_totals = array();
    protected $_currentCurrencyCode = null;
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dashboard/bar.phtml');
    }
    
    protected function getTotals()
    {
        return $this->_totals;
    }
    
    public function addTotal($label, $value, $isQuantity=false)
    {
        if (!$isQuantity) {
            $value = $this->format($value);
        }
        $decimals = '';
        $this->_totals[] = array(
            'label' => $label,
            'value' => $value,
            'decimals' => $decimals,
        );
        
        return $this;
    }
    
    /**
     * Formating value specific for this store
     *
     * @param decimal $price
     * @return string
     */
    public function format($price)
    {
        return $this->getCurrency()->format($price);
    }
    
    /**
     * Setting currency model
     *
     * @param Mage_Directory_Model_Currency $currency
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }
    
    /**
     * Retrieve currency model if not set then return currency model for current store
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrency()
    {
        if (is_null($this->_currentCurrencyCode)) {
            if ($this->getRequest()->getParam('store')) {
                $this->_currentCurrencyCode = Mage::app()->getStore($this->getRequest()->getParam('store'))->getBaseCurrency();
            } else if ($this->getRequest()->getParam('website')){
                $this->_currentCurrencyCode = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getBaseCurrency();
            } else if ($this->getRequest()->getParam('group')){
                $this->_currentCurrencyCode =  Mage::app()->getGroup($this->getRequest()->getParam('group'))->getWebsite()->getBaseCurrency();
            } else {
                $this->_currentCurrencyCode = Mage::app()->getStore()->getBaseCurrency();
            }
        }
        
        return $this->_currentCurrencyCode;
    }
}
