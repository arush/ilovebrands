<?php
class Evogue_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('gridReportCustomersegments');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('evogue_customersegment/segment')->getCollection();
        $collection->addCustomerCountToSelect();
        $collection->addWebsitesToResult();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'customer_count') {
            if ($column->getFilter()->getValue() !== null) {
                $this->getCollection()->addCustomerCountFilter($column->getFilter()->getValue());
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns() {
        parent::_prepareColumns();

        $this->addColumn('segment_id', array(
            'header'    => Mage::helper('evogue_customersegment')->__('ID'),
            'align'     =>'right',
            'width'     => 50,
            'index'     => 'segment_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Segment Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Status'),
            'align'     => 'left',
            'width'     => 80,
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        $this->addColumn('website', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Website'),
            'align'     =>'left',
            'width'     => 200,
            'index'     => 'website_ids',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
        ));

        $this->addColumn('customer_count', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Number of Customers'),
            'index'     =>'customer_count',
            'width'     => 200
        ));

        return $this;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('segment_id');
        $this->getMassactionBlock()->addItem('view', array(
            'label'=> Mage::helper('evogue_customersegment')->__('View Combined Report'),
            'url'  => $this->getUrl('*/*/detail', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                         'name'     => 'view_mode',
                         'type'     => 'select',
                         'class'    => 'required-entry',
                         'label'    => Mage::helper('evogue_customersegment')->__('Set'),
                         'values'   => Mage::helper('evogue_customersegment')->getOptionsArray()
                     )
             )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/detail', array('segment_id' => $row->getId()));
    }
}
