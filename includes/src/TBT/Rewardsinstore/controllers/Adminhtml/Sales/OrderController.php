<?php
require_once "Mage/Adminhtml/controllers/Sales/OrderController.php";
class TBT_Rewardsinstore_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    
    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('rewardsinstore/order')->polluteOrders()->load($id);
        
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
}
