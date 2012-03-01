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
 * Provides helper methods for getting analytics data about Instore orders
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Helper_Reports_Order extends TBT_Rewardsinstore_Helper_Reports_Abstract
{
    protected function _initCollection()
    {
        //$isFilter = $this->getParam('storefront') || $this->getParam('website') || $this->getParam('group');
        
        $this->_collection = Mage::getResourceModel('rewards/transfer_collection');
        $this->_collection->selectOnlyDistributions()
            ->sumPoints()
            ->addExpressionFieldToSelect(
                'range',
                $this->_getRangeExpressionForAttribute($this->getParam('period'), '{{creation_ts}}', $this->_collection),
                'creation_ts')
            ->addFieldToFilter('creation_ts', $this->getDateRange($this->getParam('period'), 0, 0))
            ->addFieldToFilter('reason_id', 1)
            ->addFieldToFilter('reference_type', array(
                'orders'  => TBT_Rewardsinstore_Model_Transfer_Reference_Quote::REFERENCE_TYPE_ID,
                'signups' => TBT_Rewardsinstore_Model_Transfer_Reference_Signup::REFERENCE_TYPE_ID));
        $this->_collection->getSelect()->reset(Zend_Db_Select::ORDER);
        $this->_collection->setOrder('`range`', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        $this->_collection->getSelect()->group('range');
        
        // TODO: Filter by website/group properly here
        if ($this->getParam('storefront')) {
            $this->_collection->addFieldToFilter('storefront_id', $this->getParam('storefront'));
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('storefront_id', array('in' => implode(',', $storeIds)));
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('storefront_id', array('in' => implode(',', $storeIds)));
        }
        
        $this->_collection->load();
    }
    
    protected function _getRangeExpression($range)
    {
        switch ($range)
        {
            case '24h':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
               $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
               break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }
        
        return $expression;
    }
    
    /**
     * Retriev range exression adapteted for attribute
     *
     * @param string $range
     * @param unknown_type $attribute
     */
    protected function _getRangeExpressionForAttribute($range, $attribute, $collection)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $collection->getConnection()->quoteIdentifier($attribute), $expression);
    }
    
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new Zend_Date(Mage::getModel('core/date')->gmtTimestamp());
        $dateStart = clone $dateEnd;
        
        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);
        
        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);
        
        switch ($range)
        {
            case '24h':
                $dateEnd = new Zend_Date(Mage::getModel('core/date')->gmtTimestamp());
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;
            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;
            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;
            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;
            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }
        
        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from'=>$dateStart, 'to'=>$dateEnd, 'datetime'=>true);
        }
    }
}
