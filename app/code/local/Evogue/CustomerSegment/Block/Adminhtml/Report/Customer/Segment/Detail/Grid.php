<?php

class Evogue_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Detail_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->setId('segmentGrid')->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('evogue_customersegment/report_customer_collection');
        $collection->addNameToSelect()
            ->setViewMode($this->getCustomerSegment()->getViewMode())
            ->addSegmentFilter($this->getCustomerSegment())
            ->addWebsiteFilter(Mage::registry('filter_website_ids'))
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
            ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getCustomerSegment() {
        return Mage::registry('current_customer_segment');
    }

    protected function _prepareColumns() {
        $this->addColumn('grid_entity_id', array(
            'header'    => Mage::helper('evogue_customersegment')->__('ID'),
            'width'     => 50,
            'index'     => 'entity_id',
            'type'      => 'number',
        ));
        $this->addColumn('grid_name', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('grid_email', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Email'),
            'width'     => 150,
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('grid_group', array(
            'header'    =>  Mage::helper('evogue_customersegment')->__('Group'),
            'width'     =>  100,
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('grid_telephone', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Telephone'),
            'width'     => 100,
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('grid_billing_postcode', array(
            'header'    => Mage::helper('evogue_customersegment')->__('ZIP'),
            'width'     => 90,
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('grid_billing_country_id', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Country'),
            'width'     => 100,
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('grid_billing_region', array(
            'header'    => Mage::helper('evogue_customersegment')->__('State/Province'),
            'width'     => 100,
            'index'     => 'billing_region',
        ));

        $this->addColumn('grid_customer_since', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Customer Since'),
            'width'     => 200,
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('evogue_customersegment')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('evogue_customersegment')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/customerGrid',
            array('segment_id' => Mage::registry('current_customer_segment')->getId()));
    }

    public function getRowUrl($item) {
        return null;
    }
}
