<?php

class Ebizmarts_SagePaySuite_Block_Adminhtml_Paymentransaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('payment_transactions_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->getCollection()->getApproved();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'=> Mage::helper('sagepaysuite')->__('ID'),
            'width' => '80px',
            'index' => 'id',
            'type' => 'number'
        ));

        $this->addColumn('order_id', array(
            'header'=> Mage::helper('sagepaysuite')->__('Order ID'),
            'width' => '80px',
            'index' => 'order_id',
            'type' => 'number'
        ));

        $this->addColumn('vendor_tx_code', array(
            'header'=> Mage::helper('sagepaysuite')->__('Vendor Tx Code'),
            'width' => '80px',
            'index' => 'vendor_tx_code'
        ));

        $this->addColumn('vendorname', array(
            'header'=> Mage::helper('sagepaysuite')->__('Vendor Name'),
            'width' => '80px',
            'index' => 'vendorname'
        ));

        $this->addColumn('mode', array(
            'header'=> Mage::helper('sagepaysuite')->__('Mode'),
            'width' => '80px',
            'index' => 'mode'
        ));

        $this->addColumn('vps_tx_id', array(
            'header'=> Mage::helper('sagepaysuite')->__('Vps Tx Id'),
            'width' => '140px',
            'index' => 'vps_tx_id'
        ));

        $this->addColumn('status', array(
            'header'=> Mage::helper('sagepaysuite')->__('Transaction Status'),
            'width' => '80px',
            'index' => 'status'
        ));

        $this->addColumn('customer_cc_holder_name', array(
            'header'=> Mage::helper('sagepaysuite')->__('Card Holder Name'),
            'width' => '80px',
            'index' => 'customer_cc_holder_name',
        ));

        $this->addColumn('customer_contact_info', array(
            'header'=> Mage::helper('sagepaysuite')->__('Customer Contact Info'),
            'width' => '80px',
            'index' => 'customer_contact_info',
        ));

        $ccCards = Mage::getModel('sagepaysuite/sagepaysuite_source_creditCards')->toOption();
        $this->addColumn('card_type', array(
            'header'=> Mage::helper('sagepaysuite')->__('Card Type'),
            'width' => '80px',
            'type'  => 'options',
            'index' => 'card_type',
            'options' => $ccCards
        ));

        $this->addColumn('last_four_digits', array(
            'header'=> Mage::helper('sagepaysuite')->__('Last 4 Digits'),
            'width' => '80px',
            'index' => 'last_four_digits'
        ));

        $this->addColumn('integration', array(
            'header'=> Mage::helper('sagepaysuite')->__('Integration'),
            'width' => '80px',
            'index' => 'integration'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sagepaysuite')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'     => 'getOrderId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sagepaysuite')->__('View Order'),
                        'url'     => array('base'=>'adminhtml/sales_order/view'),
                        'field'   => 'order_id',
                        'popup'   => '1',
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('sgpsSecure/adminhtml_transaction/paymentsGrid', array('_current'=>true));
    }

}