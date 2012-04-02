<?php
class Evogue_TargetRule_Block_Adminhtml_Targetrule_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('TargetRuleGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('evogue_targetrule/rule')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array(
            'id'    => $row->getId()
        ));
    }

    protected function _prepareColumns() {
        $this->addColumn('rule_id', array(
            'header'    => Mage::helper('evogue_targetrule')->__('ID'),
            'index'     => 'rule_id',
            'type'      => 'text',
            'width'     => 20,
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Rule Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('from_date', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Date Starts'),
            'index'     => 'from_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('to_date', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Date Ends'),
            'index'     => 'to_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Priority'),
            'index'     => 'sort_order',
            'type'      => 'text',
            'width'     => 1,
        ));

        $this->addColumn('apply_to', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Applies To'),
            'align'     => 'left',
            'index'     => 'apply_to',
            'type'      => 'options',
            'options'   => Mage::getSingleton('evogue_targetrule/rule')->getAppliesToOptions(),
            'width'     => 150,
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('evogue_targetrule')->__('Status'),
            'align'     => 'left',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('evogue_targetrule')->__('Active'),
                0 => Mage::helper('evogue_targetrule')->__('Inactive'),
            ),
            'width'     => 1,
        ));

        return $this;
    }
}
