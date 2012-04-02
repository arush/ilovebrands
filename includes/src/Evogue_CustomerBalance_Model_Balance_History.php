<?php

class Evogue_CustomerBalance_Model_Balance_History extends Mage_Core_Model_Abstract {
    const ACTION_UPDATED  = 1;
    const ACTION_CREATED  = 2;
    const ACTION_USED     = 3;
    const ACTION_REFUNDED = 4;

    protected function _construct() {
        $this->_init('evogue_customerbalance/balance_history');
    }

    public function getActionNamesArray() {
        return array(
            self::ACTION_CREATED  => Mage::helper('evogue_customerbalance')->__('Created'),
            self::ACTION_UPDATED  => Mage::helper('evogue_customerbalance')->__('Updated'),
            self::ACTION_USED     => Mage::helper('evogue_customerbalance')->__('Used'),
            self::ACTION_REFUNDED => Mage::helper('evogue_customerbalance')->__('Refunded'),
        );
    }

    protected function _beforeSave() {
        $balance = $this->getBalanceModel();
        if ((!$balance) || !$balance->getId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('Balance history cannot be saved without existing balance.'));
        }

        $this->addData(array(
            'balance_id'     => $balance->getId(),
            'updated_at'     => time(),
            'balance_amount' => $balance->getAmount(),
            'balance_delta'  => $balance->getAmountDelta(),
        ));

        switch ((int)$balance->getHistoryAction()) {
            case self::ACTION_CREATED:
                // break intentionally omitted
            case self::ACTION_UPDATED:
                if (!$balance->getUpdatedActionAdditionalInfo()) {
                    if ($user = Mage::getSingleton('admin/session')->getUser()) {
                        if ($user->getUsername()) {
                            if (!trim($balance->getComment())){
                                $this->setAdditionalInfo(Mage::helper('evogue_customerbalance')->__('By admin: %s.', $user->getUsername()));
                            }else{
                                $this->setAdditionalInfo(Mage::helper('evogue_customerbalance')->__('By admin: %1$s. (%2$s)', $user->getUsername(), $balance->getComment()));
                            }
                        }
                    }
                } else {
                    $this->setAdditionalInfo($balance->getUpdatedActionAdditionalInfo());
                }
                break;
            case self::ACTION_USED:
                $this->_checkBalanceModelOrder($balance);
                $this->setAdditionalInfo(Mage::helper('evogue_customerbalance')->__('Order #%s', $balance->getOrder()->getIncrementId()));
                break;
            case self::ACTION_REFUNDED:
                $this->_checkBalanceModelOrder($balance);
                if ((!$balance->getCreditMemo()) || !$balance->getCreditMemo()->getIncrementId()) {
                    Mage::throwException(Mage::helper('evogue_customerbalance')->__('There is no creditmemo set to balance model.'));
                }
                $this->setAdditionalInfo(Mage::helper('evogue_customerbalance')->__('Order #%s, creditmemo #%s',
                    $balance->getOrder()->getIncrementId(), $balance->getCreditMemo()->getIncrementId()
                ));
                break;
            default:
                Mage::throwException(Mage::helper('evogue_customerbalance')->__('Unknown balance history action code'));
                // break intentionally omitted
        }
        $this->setAction((int)$balance->getHistoryAction());

        return parent::_beforeSave();
    }


    protected function _afterSave() {
        parent::_afterSave();

        // attempt to send email
        $this->setIsCustomerNotified(false);
        if ($this->getBalanceModel()->getNotifyByEmail()) {
            $storeId = $this->getBalanceModel()->getStoreId();
            $email = Mage::getModel('core/email_template')->setDesignConfig(array('store' => $storeId));
            $customer = $this->getBalanceModel()->getCustomer();
            $email->sendTransactional(
                Mage::getStoreConfig('customer/evogue_customerbalance/email_template', $storeId),
                Mage::getStoreConfig('customer/evogue_customerbalance/email_identity', $storeId),
                $customer->getEmail(), $customer->getName(),
                array(
                    'balance' => Mage::app()->getWebsite($this->getBalanceModel()->getWebsiteId())->getBaseCurrency()->format($this->getBalanceModel()->getAmount(), array(), false),
                    'name'    => $customer->getName(),
            ));
            if ($email->getSentSuccess()) {
                $this->getResource()->markAsSent($this->getId());
                $this->setIsCustomerNotified(true);
            }
        }

        return $this;
    }

    protected function _checkBalanceModelOrder($model) {
        if ((!$model->getOrder()) || !$model->getOrder()->getIncrementId()) {
            Mage::throwException(Mage::helper('evogue_customerbalance')->__('There is no order set to balance model.'));
        }
    }
}
