<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class TBT_RewardsApi_Model_Api extends Mage_Sales_Model_Api_Resource {

    public function getCustomerIdByEmail($customer_email, $website_id=1) {
        $this->_noStFault();
        $customer = Mage::getModel('rewards/customer')->setWebsiteId((int) $website_id);
        $customer = $customer->loadByEmail($customer_email);
        if (!$customer->getId()) {
            $msg = Mage::helper('rewardsapi')->__("No such customer with email %s exists in website #%s.", $customer_email, $website_id);
            $this->_fault('no_such_customer', $msg);
        }
        return $customer->getId();
    }

    public function getBalanceByEmail($customer_email, $website_id=1) {
        $this->_noStFault();
        $customer = Mage::getModel('rewards/customer')->setWebsiteId((int) $website_id);
        $customer = $customer->loadByEmail($customer_email);
        if (!$customer->getId()) {
            $msg = Mage::helper('rewardsapi')->__("No such customer with email %s exists in website #%s.", $customer_email, $website_id);
            $this->_fault('no_such_customer', $msg);
        }
        $customer = $customer->load($customer->getId());
        return $customer->getUsablePoints();
    }

    public function getBalanceById($customer_id) {
        $this->_noStFault();
        $customer = Mage::getModel('rewards/customer');
        $customer = $customer->load($customer_id);
        if (!$customer->getId()) {
            $msg = Mage::helper('rewardsapi')->__("No such customer with id %s exists.", $customer_id);
            $this->_fault('no_such_customer', $msg);
        }
        return $customer->getUsablePoints();
    }

    public function makeTransfer($customer_id, $points_currency_id, $points_quantity, $comments="", $status=null, $reason=null) {
        $this->_noStFault();
        try {
            if ($status == null)
                $status = TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED;
            if ($reason == null)
                $reason = TBT_Rewards_Model_Transfer_Reason::REASON_ADMIN_ADJUSTMENT;
            //load in transfer model
            $transfer = Mage::getModel('rewards/transfer');
            //Load it up with information
            $transfer->setId(null)
                    ->setCustomerId($customer_id) // the id of the customer that these points will be going out to
                    ->setCurrencyId($points_currency_id) // in versions of sweet tooth 1.0-1.2 this should be set to "1"
                    ->setQuantity($points_quantity) // number of points to transfer.  This number can be negative or positive, but not zero
                    ->setComments($comments); //This is optional
            $transfer->setReasonId($reason);
            //Checks to make sure you can actually move the transfer into the new status
            if ($transfer->setStatus(null, $status)) { // STATUS_APPROVED would transfer the points in the approved status to the customer
                $transfer->save(); //Save everything and execute the transfer
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_fault('transfer_exception', $e->getMessage());
        }
    }

    public function _noStFault() {
        try {
            if (Mage::getConfig()->getModuleConfig('TBT_Rewards')->is('active', 'false')) {
                throw new Exception(Mage::helper('rewardsapi')->__("Sweet Tooth must be installed on the server in order to use the Sweet Tooth API"));
            }
        } catch (Exception $e) {
            $this->_fault('api_usage_exception', $e->getMessage());
        }
        return $this;
    }

}

// Class Mage_Sales_Model_Order_Api End