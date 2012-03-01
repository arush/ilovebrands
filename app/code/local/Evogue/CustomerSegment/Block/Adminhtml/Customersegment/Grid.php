<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('customersegmentGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('evogue_customersegment/segment')->getCollection();
        $collection->addWebsitesToResult();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('grid_segment_id', array(
            'header'    => Mage::helper('evogue_customersegment')->__('ID'),
            'align'     =>'right',
            'width'     => 50,
            'index'     => 'segment_id',
        ));

        $this->addColumn('grid_segment_name', array(
            'header'    => Mage::helper('evogue_customersegment')->__('Segment Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('grid_segment_is_active', array(
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

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('grid_segment_website', array(
                'header'    => Mage::helper('evogue_customersegment')->__('Website'),
                'align'     =>'left',
                'index'     => 'website_ids',
                'type'      => 'options',
                'sortable'  => false,
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width'     => 200,
            ));
        }

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        if ($this->getIsChooserMode()) {
            return null;
        }
        return $this->getUrl('*/*/edit', array('id' => $row->getSegmentId()));
    }

    public function getRowClickCallback() {
        if ($this->getIsChooserMode() && $elementId = $this->getRequest()->getParam('value_element_id')) {
            return 'function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                if (trElement) {
                    $(\'' . $elementId . '\').value = trElement.down("td").innerHTML;
                    $(grid.containerId).up().hide();
                }}';
        }
        return 'openGridRow';
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/customersegment/grid', array('_current' => true));
    }
}
