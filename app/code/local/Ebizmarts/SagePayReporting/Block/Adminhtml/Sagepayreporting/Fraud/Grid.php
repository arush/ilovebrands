<?php

class Ebizmarts_SagePayReporting_Block_Adminhtml_Sagepayreporting_Fraud_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    const IMAGE_RENDERER = 'Ebizmarts_SagePaySuite_Block_Adminhtml_Widget_Grid_Column_Renderer_Image';
    const TEXT_FLAG_RENDERER = 'Ebizmarts_SagePaySuite_Block_Adminhtml_Widget_Grid_Column_Renderer_Textflag';

    public function __construct()
    {
        parent::__construct();
        $this->setId('sagepayreporting_fraud');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _getCheckFlag($value, $row)
    {
        $image = '';
        switch ($value) {
            case 1:
                $image = $this->getSkinUrl('images/sagepaysuite/flag_green.png');
                break;
            case 0:
                $image = $this->getSkinUrl('images/sagepaysuite/flag_red.png');
                break;
            default:
                $image = $this->getSkinUrl('images/sagepaysuite/flag_blue.png');
                break;
        }

        return $image;
    }

    public function getFraud2($orderId)
    {
        return Mage::helper('sagepaysuite/paymentinfo')->getFraudInfo($orderId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $collection->getSelect()->joinLeft(
            array('pmnt' => $collection->getTable('sales/order_payment')), # sales_flat_order_payment
            'main_table.entity_id = pmnt.parent_id',
            array()
        )->joinLeft(
            array('sls' => $collection->getTable('sales/order')), # sales_flat_order
            'main_table.entity_id = sls.entity_id',
            array()
        )->joinLeft(
            array('ste' => Mage :: getSingleton('core/resource')->getTableName('sagepaysuite_transaction')),
            'main_table.entity_id = ste.order_id',
            array('released', 'tx_type', 'authorised', 'canceled', 'aborted')
        )
        ->where('pmnt.method = ?', 'sagepayserver')
        ->orWhere('pmnt.method = ?', 'sagepaydirectpro')
        ->orWhere('pmnt.method = ?', 'sagepaydirectpro_moto')
        ->orWhere('pmnt.method = ?', 'sagepayserver_moto')
        ->orWhere('pmnt.method = ?', 'sagepayform')
        ->orWhere('pmnt.method = ?', 'sagepaypaypal');

        $this->setCollection($collection);
        parent::_prepareCollection();

        foreach($collection as $_c){

            $f = $this->getFraud2($_c->getId());

            $_c->setData('cv2', $f->getData('cv2result'));
            $_c->setData('postcode', $f->getData('postcoderesult'));
            $_c->setData('address_result', $f->getData('addressresult'));
            $_c->setData('thirdman_score', $f->getThirdmanScore());
            $_c->setData('thirdman_action', $f->getThirdmanAction());
            $_c->setData('thirdman_id', $f->getThirdmanId());
            $_c->setData('tresd', $f->getTresd());

            if($_c->getTxType() == 'PAYMENT'){
				$_c->setPaymentStatus(Mage::helper('sagepaysuite')->__('OK Captured'));
            }else if($_c->getTxType() == 'DEFERRED'){
            	if($_c->getReleased()){
            		$_c->setPaymentStatus(Mage::helper('sagepaysuite')->__('OK Released'));
            	}else{
            		$_c->setPaymentStatus(Mage::helper('sagepaysuite')->__('NOT Released'));
            	}
            }else if($_c->getTxType() == 'AUTHENTICATE'){
            	if($_c->getAuthorised()){
            		$_c->setPaymentStatus(Mage::helper('sagepaysuite')->__('OK Authorised'));
            	}else{
            		$_c->setPaymentStatus(Mage::helper('sagepaysuite')->__('NOT Authorised'));
            	}
            }

        }
        $this->setDefaultSort('increment_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

		/**
		 * TODO: FIX FILTER by STORE
		 */
        /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }*/

        $this->addColumn('cv2', array(
            'header' => Mage::helper('sales')->__('Verification Value (CV2)'),
            'index' => 'cv2',
            'type' => 'text',
            'align'     => 'center',
            'renderer'  => self::TEXT_FLAG_RENDERER,
            'filter' => false,
            'sortable'  => false
        ));

        $this->addColumn('postcode', array(
            'header' => Mage::helper('sales')->__('Post Code'),
            'index' => 'postcode',
            'align'     => 'center',
            'renderer'  => self::TEXT_FLAG_RENDERER,
            'filter' => false,
            'sortable'  => false
        ));

        $this->addColumn('address_result', array(
            'header' => Mage::helper('sales')->__('Address Numerics'),
            'index' => 'address_result',
            'align'     => 'center',
            'renderer'  => self::TEXT_FLAG_RENDERER,
            'filter' => false,
            'sortable'  => false
        ));

        $this->addColumn('thirdmanaction', array(
            'header' => Mage::helper('sales')->__('The 3rdMan Action'),
            'index' => 'thirdman_action',
            'align'     => 'center',
            'renderer'  => self::TEXT_FLAG_RENDERER,
            'filter' => false,
            'sortable'  => false
        ));

        $this->addColumn('thirdman', array(
            'header' => Mage::helper('sales')->__('The 3rdMan Score'),
            'index' => 'thirdman_score',
            'align'     => 'center',
            'filter' => false,
            'sortable'  => false
        ));

        /*$this->addColumn('thirdmanid', array(
            'header' => Mage::helper('sales')->__('The 3rdMan ID'),
            'index' => 'thirdman_id',
            'align'     => 'center',
            'filter' => false,
            'sortable'  => false
        ));*/

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('payment_status', array(
            'header' => Mage::helper('sagepaysuite')->__('Payment Status'),
            'index' => 'payment_status',
            'filter' => false,
            'align'     => 'center',
            'sortable'  => false,
            'renderer' => self::IMAGE_RENDERER,
        ));


        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '70px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sagepaysuite')->__('Check 3rd Man'),
                        'url'     => array('base'=>'*/*/fraudCheck'),
                        'field'   => 'order_id',
                    ),
                    array(
                        'caption' => Mage::helper('sagepaysuite')->__('Release / Authorise'),
                        'url'     => array('base'=>'*/*/invoice'),
                        'field'   => 'order_id',
                        'confirm'   => Mage::helper('sagepaysuite')->__('Are you sure you want to do this?'),
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('tm_check', array(
             'label'=> Mage::helper('sagepaysuite')->__('Check 3rd Man'),
             'url'  => $this->getUrl('*/*/fraudCheck'),
        ));

        $this->getMassactionBlock()->addItem('inv_ord', array(
             'label'=> Mage::helper('sagepaysuite')->__('Release / Authorise'),
             'url'  => $this->getUrl('*/*/invoice'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}