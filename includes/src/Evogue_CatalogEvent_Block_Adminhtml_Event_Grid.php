<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('catalogEventGrid');
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
           $collection = Mage::getModel('evogue_catalogevent/event')->getCollection()
               ->addCategoryData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('event_id', array(
            'header'=> Mage::helper('evogue_catalogevent')->__('ID'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'event_id'
        ));

        $this->addColumn('category_id', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Category ID'),
            'index' => 'category_id',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('category', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Category'),
            'index' => 'category_name',
            'type'  => 'text'
        ));

        $this->addColumn('date_start', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Starts On'),
            'index' => 'date_start',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('date_end', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Ends On'),
            'index' => 'date_end',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                Evogue_CatalogEvent_Model_Event::STATUS_UPCOMING => Mage::helper('evogue_catalogevent')->__('Upcoming'),
                Evogue_CatalogEvent_Model_Event::STATUS_OPEN 	 => Mage::helper('evogue_catalogevent')->__('Open'),
                Evogue_CatalogEvent_Model_Event::STATUS_CLOSED   => Mage::helper('evogue_catalogevent')->__('Closed')
            ),
            'width' => 140
        ));

        $this->addColumn('display_state', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Display Countdown Ticker On'),
            'index' => 'display_state',
            'type' => 'options',
            'renderer' => 'evogue_catalogevent/adminhtml_event_grid_column_renderer_bitmask',
            'options' => array(
                0 => Mage::helper('evogue_catalogevent')->__('Lister Block'),
                Evogue_CatalogEvent_Model_Event::DISPLAY_CATEGORY_PAGE => Mage::helper('evogue_catalogevent')->__('Category Page'),
                Evogue_CatalogEvent_Model_Event::DISPLAY_PRODUCT_PAGE  => Mage::helper('evogue_catalogevent')->__('Product Page')
            )
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('evogue_catalogevent')->__('Sort Order'),
            'index' => 'sort_order',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('actions', array(
            'header'    => $this->helper('evogue_catalogevent')->__('Action'),
            'width'     => 15,
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'action',
            'actions'   => array(
                array(
                    'url'       => $this->getUrl('*/*/edit') . 'id/$event_id',
                    'caption'   => $this->helper('evogue_catalogevent')->__('Edit'),
                ),
            )
        ));

        return parent::_prepareColumns();
    }


    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
