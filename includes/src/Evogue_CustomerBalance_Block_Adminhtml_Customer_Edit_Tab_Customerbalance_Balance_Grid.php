<?php
class Evogue_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance_Balance_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('balanceGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('name');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('evogue_customerbalance/balance')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('amount', array(
            'header'   => Mage::helper('evogue_customerbalance')->__('Balance'),
            'width'    => 50,
            'index'    => 'amount',
            'sortable' => false,
            'renderer' => 'evogue_customerbalance/adminhtml_widget_grid_column_renderer_currency',
        ));

        $this->addColumn('website_id', array(
            'header'   => Mage::helper('evogue_customerbalance')->__('Website'),
            'index'    => 'website_id',
            'sortable' => false,
            'type'     => 'options',
            'options'  => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
        ));

        return parent::_prepareColumns();
    }
}
