<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('simplegoogleShoppingGrid');
        $this->setDefaultSort('simplegoogleshopping_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('simplegoogleshopping/simplegoogleshopping')->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('simplegoogleshopping_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'simplegoogleshopping_id',
        ));

        $this->addColumn('simplegoogleshopping_filename', array(
            'header' => $this->__('Simplegoogleshopping filename'),
            'align' => 'left',
            'index' => 'simplegoogleshopping_filename',
        ));

        $this->addColumn('simplegoogleshopping_path', array(
            'header' => $this->__('File path'),
            'align' => 'left',
            'index' => 'simplegoogleshopping_path',
        ));

        $this->addColumn('link', array(
            'header' => $this->__('File link'),
            'align' => 'left',
            'index' => 'link',
            "filter"=>false,
            "sortable"=>false,
            'renderer' => 'Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Link',
        ));
        $this->addColumn('simplegoogleshopping_time', array(
            'header' => $this->__('Last update'),
            'align' => 'left',
            'index' => 'simplegoogleshopping_time',
            'type' => 'datetime',
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => $this->__('Store View'),
                'index' => 'store_id',
                'type' => 'store',
            ));
        }


        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'align' => 'left',
            'index' => 'action',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Action',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}