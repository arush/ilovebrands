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
class TBT_Rewardsinstore_Model_Order extends TBT_Rewards_Model_Sales_Order
{
    protected $_eventPrefix = 'rewardsinstore_order';
    protected $_eventObject = 'order';
    
    protected $_doSaveBaseOrder = true;
    protected $_areOrdersPolluted = false;
    
    protected function _construct()
    {
        $this->_init('rewardsinstore/order');
    }
    
    /**
     * Sets whether or not to save the base sales/order model when saving this model
     *
     * @param bool $flag
     * @return TBT_Rewardsinstore_Model_Order
     */
    public function setDoSaveBaseOrder($flag = true)
    {
        $this->_doSaveBaseOrder = $flag;
        return $this;
    }
    
    /**
     * Allows non-Instore orders to be loaded into this model
     *
     * @param bool $flag
     * @return TBT_Rewardsinstore_Model_Order
     */
    public function polluteOrders($flag = true)
    {
        $this->_areOrdersPolluted = $flag;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getOrderPollution()
    {
        return $this->_areOrdersPolluted;
    }
    
    /**
     * Save object data.
     * Specifically, only save the Instore-specific data and salesorder reference.
     *
     * @return TBT_Rewardsinstore_Model_Order
     */
    public function save()
    {
        if ($this->_doSaveBaseOrder) {
            $base_order = Mage::getModel('sales/order')
                ->setData($this->getData())
                ->setId($this->getId())
                ->save();
            
            if (is_null($this->getId())) {
                $this->isObjectNew(true);
            }
            $this->setId($base_order->getId());
        }
        
        $this->setForceUpdateGridRecords(true);
        return parent::save();
    }
    
    /**
     * Loads order data (Instore order combined with base order)
     *
     * @return TBT_Rewardsinstore_Model_Order
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        
        $this->setIsInstore(true);
        if(!$this->getId()) {
            $this->setId($this->getEntityId())
                ->setIsInstore(false);
        }
        
        return $this;
    }
    
    /**
     * Delete Instore order and associated salesorder from database
     *
     * @return TBT_Rewardsinstore_Model_Order
     */
    public function delete()
    {
        $order_id = $this->getId();
        
        parent::delete();
        
        // Delete base order right after our Instore order is deleted
        Mage::getModel('sales/order')
            ->load($order_id)
            ->delete();
        
        return $this;
    }
}
