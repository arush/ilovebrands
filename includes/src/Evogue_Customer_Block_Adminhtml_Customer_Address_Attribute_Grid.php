<?php
class Evogue_Customer_Block_Adminhtml_Customer_Address_Attribute_Grid
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('sort_order');
        $this->setId('customerAddressAttributeGrid');
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('customer/address_attribute_collection')
            ->addSystemHiddenFilter()
            ->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        parent::_prepareColumns();

        $this->addColumn('is_visible', array(
            'header'    => Mage::helper('evogue_customer')->__('Visible on Frontend'),
            'sortable'  => true,
            'index'     => 'is_visible',
            'type'      => 'options',
            'options'   => array(
                '0' => Mage::helper('evogue_customer')->__('No'),
                '1' => Mage::helper('evogue_customer')->__('Yes'),
            ),
            'align'     => 'center',
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('evogue_customer')->__('Sort Order'),
            'sortable'  => true,
            'align'     => 'center',
            'index'     => 'sort_order'
        ));

        return $this;
    }
}
