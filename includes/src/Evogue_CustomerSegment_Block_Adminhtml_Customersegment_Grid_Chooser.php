<?php
class Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Grid_Chooser
    extends Evogue_CustomerSegment_Block_Adminhtml_Customersegment_Grid {

    public function __construct() {
        parent::__construct();
        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('customersegment_grid_chooser_'.$this->getId());
        }

        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

        $form = $this->getRequest()->getParam('form');
        if ($form) {
            $this->setRowClickCallback("$form.chooserGridRowClick.bind($form)");
            $this->setCheckboxCheckCallback("$form.chooserGridCheckboxCheck.bind($form)");
            $this->setRowInitCallback("$form.chooserGridRowInit.bind($form)");
        }
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    public function getRowClickCallback() {
        return $this->_getData('row_click_callback');
    }

    protected function _prepareColumns() {
        $this->addColumn('in_segments', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_segments',
            'values'    => $this->_getSelectedSegments(),
            'align'     => 'center',
            'index'     => 'segment_id',
            'use_index' => true,
        ));
        return parent::_prepareColumns();
    }

    protected function _getSelectedSegments() {
        $segments = $this->getRequest()->getPost('selected', array());
        return $segments;
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/customersegment/chooserGrid', array('_current' => true));
    }
}
