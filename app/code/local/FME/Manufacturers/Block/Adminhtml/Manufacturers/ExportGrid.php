<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Manufacturers_Block_Adminhtml_Manufacturers_ExportGrid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('manufacturersExportGrid');
      $this->setDefaultSort('manufacturers_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {	  
	  $collection = Mage::getModel('manufacturers/manufacturers')->getCollection();
      $collection->getSelect()
            ->joinLeft(array('mp' => $collection->getTable('manufacturers_products')),
                        'main_table.manufacturers_id=mp.manufacturers_id AND (mp.product_id != 0)',
                        array(
                            'products_count' => new Zend_Db_Expr('count(mp.product_id)'),
							'product_ids' => new Zend_Db_Expr('GROUP_CONCAT(mp.product_id)'),
                        )
                )
            ->group('main_table.manufacturers_id');
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
	  
  }

  protected function _prepareColumns()
  {
    
	
	$this->addColumn('m_name', array(
	  'header'    => Mage::helper('manufacturers')->__('Name'),
	  'align'     =>'left',
	  'index'     => 'm_name',
	));
	
	$this->addColumn('identifier', array(
	  'header'    => Mage::helper('manufacturers')->__('Identifier'),
	  'align'     =>'left',
	  'index'     => 'identifier',
	));
	
	$this->addColumn('m_website', array(
	  'header'    => Mage::helper('manufacturers')->__('Website'),
	  'align'     =>'left',
	  'index'     => 'm_website',
	));
	
	$this->addColumn('m_address', array(
	  'header'    => Mage::helper('manufacturers')->__('Address'),
	  'align'     =>'left',
	  'index'     => 'm_address',
	));
	
	$this->addColumn('m_logo', array(
	  'header'    => Mage::helper('manufacturers')->__('Logo'),
	  'align'     =>'left',
	  'index'     => 'm_logo',
	));
	
	$this->addColumn('m_featured', array(
	  'header'    => Mage::helper('manufacturers')->__('Logo'),
	  'align'     =>'left',
	  'index'     => 'm_featured',
	));
	
	$this->addColumn('m_featured', array(
	  'header'    => Mage::helper('manufacturers')->__('Featured'),
	  'align'     =>'left',
	  'index'     => 'm_featured',
	));
	  
	$this->addColumn('m_contact_name', array(
	  'header'    => Mage::helper('manufacturers')->__('Contact Name'),
	  'align'     =>'left',
	  'index'     => 'm_contact_name',
	));
	
	$this->addColumn('m_contact_phone', array(
	  'header'    => Mage::helper('manufacturers')->__('Contact Phone'),
	  'align'     =>'left',
	  'index'     => 'm_contact_phone',
	));
	
	$this->addColumn('m_contact_fax', array(
	  'header'    => Mage::helper('manufacturers')->__('Contact Fax'),
	  'align'     =>'left',
	  'index'     => 'm_contact_fax',
	));
	 
	$this->addColumn('m_contact_email', array(
	  'header'    => Mage::helper('manufacturers')->__('Contact Email'),
	  'align'     =>'left',
	  'index'     => 'm_contact_email',
	));
	  
	$this->addColumn('m_contact_address', array(
	  'header'    => Mage::helper('manufacturers')->__('Contact Address'),
	  'align'     =>'left',
	  'index'     => 'm_contact_address',
	));
	
	$this->addColumn('m_details', array(
	  'header'    => Mage::helper('manufacturers')->__('Manufacturer Details'),
	  'align'     =>'left',
	  'index'     => 'm_details',
	));
	
	$this->addColumn('status', array(
          'header'    => Mage::helper('manufacturers')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => '1',
              2 => '2',
          ),
     ));
	
	 $this->addColumn('product_ids', array(
          'header'    => Mage::helper('manufacturers')->__('Products'),
          'align'     =>'left',
          'index'     => 'product_ids'
      ));
	
	   	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('manufacturers')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('manufacturers')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('manufacturers')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('manufacturers')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('manufacturers_id');
        $this->getMassactionBlock()->setFormFieldName('manufacturers');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('manufacturers')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('manufacturers')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('manufacturers/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('manufacturers')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('manufacturers')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  protected function _afterLoadCollection()
    {
        
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
        
    }
    
    
    protected function _filterStoreCondition($collection, $column)
    {
        
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
    
        $this->getCollection()->addStoreFilter($value);
        
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}