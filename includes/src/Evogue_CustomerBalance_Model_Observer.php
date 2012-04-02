<?php
class Evogue_CustomerBalance_Model_Observer {
    
    public function prepareCustomerBalanceSave($observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getCustomer();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getRequest();
        if ($data = $request->getPost('customerbalance')) {
            $customer->setCustomerBalanceData($data);
        }
    }

    public function customerSaveAfter($observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }
        if ($data = $observer->getCustomer()->getCustomerBalanceData()) {
            if (!empty($data['amount_delta'])) {
                $balance = Mage::getModel('evogue_customerbalance/balance')
                    ->setCustomer($observer->getCustomer())
                    ->setWebsiteId(isset($data['website_id']) ? $data['website_id'] : $observer->getCustomer()->getWebsiteId())
                    ->setAmountDelta($data['amount_delta'])
                    ->setComment($data['comment'])
                ;
                if (isset($data['notify_by_email']) && isset($data['store_id'])) {
                    $balance->setNotifyByEmail(true, $data['store_id']);
                }
                $balance->save();
            }
        }
    }

    public function paymentDataImport(Varien_Event_Observer $observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }

        $input = $observer->getEvent()->getInput();
        $payment = $observer->getEvent()->getPayment();
        $this->_importPaymentData($payment->getQuote(), $input, $input->getUseCustomerBalance());
    }

    public function processBeforeOrderPlace(Varien_Event_Observer $observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseCustomerBalanceAmount() > 0) {
            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            $balance = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->loadByCustomer()
                ->getAmount();

            if (($order->getBaseCustomerBalanceAmount() - $balance) >= 0.0001) {
                Mage::getSingleton('checkout/type_onepage')
                    ->getCheckout()
                    ->setUpdateSection('payment-method')
                    ->setGotoSection('payment');

                Mage::throwException(Mage::helper('evogue_customerbalance')->__('Not enough Store Credit Amount to complete this Order.'));
            }
        }
    }

    public function processOrderPlace(Varien_Event_Observer $observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseCustomerBalanceAmount() > 0) {
            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();
            $balance = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta(-$order->getBaseCustomerBalanceAmount())
                ->setHistoryAction(Evogue_CustomerBalance_Model_Balance_History::ACTION_USED)
                ->setOrder($order)
                ->save();
        }
    }

    public function disableLayout($observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            unset($observer->getUpdates()->evogue_customerbalance);
        }
    }

    public function processOrderCreationData(Varien_Event_Observer $observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return $this;
        }
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();
        if (isset($request['payment']) && isset($request['payment']['use_customer_balance'])) {
            $this->_importPaymentData($quote, $quote->getPayment(),
                (bool)(int)$request['payment']['use_customer_balance']);
        }
    }

    protected function _importPaymentData($quote, $payment, $shouldUseBalance) {
        $store = Mage::app()->getStore($quote->getStoreId());
        if (!$quote || !$quote->getCustomerId()) {
            return;
        }
        $quote->setUseCustomerBalance($shouldUseBalance);
        if ($shouldUseBalance) {
            $balance = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomerId($quote->getCustomerId())
                ->setWebsiteId($store->getWebsiteId())
                ->loadByCustomer();
            if ($balance) {
                $quote->setCustomerBalanceInstance($balance);
                if (!$payment->getMethod()) {
                    $payment->setMethod('free');
                }
            }
            else {
                $quote->setUseCustomerBalance(false);
            }
        }
    }

    public function togglePaymentMethods($observer) {
        if (!Mage::helper('evogue_customerbalance')->isEnabled()) {
            return;
        }
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }
        $balance = $quote->getCustomerBalanceInstance();
        if (!$balance) {
            return;
        }

        // disable all payment methods and enable only Zero Subtotal Checkout
        if ($balance->isFullAmountCovered($quote)) {
            $result = $observer->getEvent()->getResult();
            if ('free' === $observer->getEvent()->getMethodInstance()->getCode()) {
                $result->isAvailable = true;
            } else {
                $result->isAvailable = false;
            }
        }
    }

    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        $quote->setCustomerBalanceCollected(false);
    }

    public function quoteMergeAfter(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getUseCustomerBalance()) {
            $quote->setUseCustomerBalance($source->getUseCustomerBalance());
        }
    }

    public function increaseOrderInvoicedAmount(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

        if ($invoice->getBaseCustomerBalanceAmount()) {
            $order->setBaseCustomerBalanceInvoiced($order->getBaseCustomerBalanceInvoiced() + $invoice->getBaseCustomerBalanceAmount());
            $order->setCustomerBalanceInvoiced($order->getCustomerBalanceInvoiced() + $invoice->getCustomerBalanceAmount());
        }
        $order->getResource()->saveAttribute($order, 'base_customer_balance_invoiced');
        $order->getResource()->saveAttribute($order, 'customer_balance_invoiced');
        return $this;
    }

    public function creditmemoSaveAfter(Varien_Event_Observer $observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        if ($creditmemo->getAutomaticallyCreated()) {
            if (Mage::helper('evogue_customerbalance')->isAutoRefundEnabled()) {
                $creditmemo->setCustomerBalanceRefundFlag(true)
                    ->setCustomerBalanceTotalRefunded($creditmemo->getCustomerBalanceAmount())
                    ->setBaseCustomerBalanceTotalRefunded($creditmemo->getBaseCustomerBalanceAmount());
            } else {
                return $this;
            }
        }

        if ($creditmemo->getCustomerBalanceTotalRefunded() > $creditmemo->getCustomerBalanceReturnMax()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Store credit amount can not exceed order amount.'));
        }
        if ($creditmemo->getCustomerBalanceRefundFlag() && $creditmemo->getBaseCustomerBalanceTotalRefunded()) {
            $order->setBaseCustomerBalanceTotalRefunded($order->getBaseCustomerBalanceTotalRefunded() + $creditmemo->getBaseCustomerBalanceTotalRefunded());
            $order->setCustomerBalanceTotalRefunded($order->getCustomerBalanceTotalRefunded() + $creditmemo->getCustomerBalanceTotalRefunded());

            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            $balance = Mage::getModel('evogue_customerbalance/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta($creditmemo->getBaseCustomerBalanceTotalRefunded())
                ->setHistoryAction(Evogue_CustomerBalance_Model_Balance_History::ACTION_REFUNDED)
                ->setOrder($order)
                ->setCreditMemo($creditmemo)
                ->save();
        }

        return $this;
    }

    public function creditmemoDataImport(Varien_Event_Observer $observer) {
        $request = $observer->getEvent()->getRequest();
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $request->getParam('creditmemo');

        if (isset($input['refund_customerbalance_return']) && isset($input['refund_customerbalance_return_enable'])) {
            $enable = $input['refund_customerbalance_return_enable'];
            $amount = $input['refund_customerbalance_return'];
            if ($enable && is_numeric($amount)) {
                $amount = max(0, min($creditmemo->getBaseCustomerBalanceReturnMax(), $amount));
                if ($amount) {
                    $amount = $creditmemo->getStore()->roundPrice($amount);
                    $creditmemo->setBaseCustomerBalanceTotalRefunded($amount);

                    $amount = $creditmemo->getStore()->roundPrice(
                        $amount*$creditmemo->getOrder()->getStoreToOrderRate()
                    );
                    $creditmemo->setCustomerBalanceTotalRefunded($amount);
                    //setting flag to make actual refund to customer balance after creditmemo save
                    $creditmemo->setCustomerBalanceRefundFlag(true);

                    $creditmemo->setPaymentRefundDisallowed(true);
                }
            }
        }

        if (isset($input['refund_customerbalance']) && $input['refund_customerbalance']) {
            $creditmemo->setRefundCustomerBalance(true);
        }

        if (isset($input['refund_real_customerbalance']) && $input['refund_real_customerbalance']) {
            $creditmemo->setRefundRealCustomerBalance(true);
            $creditmemo->setPaymentRefundDisallowed(true);
        }

        return $this;
    }

    public function salesOrderLoadAfter(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
            return $this;
        }

        if ($order->getCustomerBalanceInvoiced() - $order->getCustomerBalanceRefunded() > 0) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }

    public function refund(Varien_Event_Observer $observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();


        if ($creditmemo->getRefundRealCustomerBalance() && $creditmemo->getBaseGrandTotal()) {
            $baseAmount = $creditmemo->getBaseGrandTotal();
            $amount = $creditmemo->getGrandTotal();

            $creditmemo->setBaseCustomerBalanceTotalRefunded($creditmemo->getBaseCustomerBalanceTotalRefunded() + $baseAmount);
            $creditmemo->setCustomerBalanceTotalRefunded($creditmemo->getCustomerBalanceTotalRefunded() + $amount);
        }

        if ($creditmemo->getBaseCustomerBalanceAmount()) {
            if ($creditmemo->getRefundCustomerBalance()) {
                $baseAmount = $creditmemo->getBaseCustomerBalanceAmount();
                $amount = $creditmemo->getCustomerBalanceAmount();

                $creditmemo->setBaseCustomerBalanceTotalRefunded($creditmemo->getBaseCustomerBalanceTotalRefunded() + $baseAmount);
                $creditmemo->setCustomerBalanceTotalRefunded($creditmemo->getCustomerBalanceTotalRefunded() + $amount);
            }

            $order->setBaseCustomerBalanceRefunded($order->getBaseCustomerBalanceRefunded() + $creditmemo->getBaseCustomerBalanceAmount());
            $order->setCustomerBalanceRefunded($order->getCustomerBalanceRefunded() + $creditmemo->getCustomerBalanceAmount());

            if ($order->getCustomerBalanceInvoiced() > 0 && $order->getCustomerBalanceInvoiced() == $order->getCustomerBalanceRefunded()) {
                $order->setForcedCanCreditmemo(false);
            }
        }

        return $this;
    }

    public function predispatchPrepareLogging($action) {
        $request = Mage::app()->getRequest();
        $data = $request->getParam('customerbalance');
        if (isset($data['amount_delta']) && $data['amount_delta'] != '') {
            $actions = Mage::registry('evogue_logged_actions');
            if (!is_array($actions)) {
                $actions = array($actions);
            }
            $actions[] = 'adminhtml_customerbalance_save';
            Mage::unregister('evogue_logged_actions');
            Mage::register('evogue_logged_actions', $actions);
        }
    }

    public function setCustomersBalanceCurrencyToWebsiteBaseCurrency(Varien_Event_Observer $observer) {
        Mage::getModel('evogue_customerbalance/balance')->setCustomersBalanceCurrencyTo(
            $observer->getEvent()->getWebsite()->getWebsiteId(),
            $observer->getEvent()->getWebsite()->getBaseCurrencyCode()
        );
        return $this;
    }

    public function addPaypalCustomerBalanceItem(Varien_Event_Observer $observer) {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            if ($salesEntity instanceof Mage_Sales_Model_Quote) {
                $balanceField = 'base_customer_balance_amount_used';
            } elseif ($salesEntity instanceof Mage_Sales_Model_Order) {
                $balanceField = 'base_customer_balance_amount';
            } else {
                return;
            }

            $value = abs($salesEntity->getDataUsingMethod($balanceField));
            if ($value > 0.0001) {
                $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, (float)$value,
                    Mage::helper('evogue_customerbalance')->__('Store Credit (%s)', Mage::app()->getStore()->convertPrice($value, true, false))
                );
            }
        }
    }
}
