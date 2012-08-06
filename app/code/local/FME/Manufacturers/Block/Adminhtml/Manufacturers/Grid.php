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

class FME_Manufacturers_Block_Adminhtml_Manufacturers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('manufacturersGrid');
      $this->setDefaultSort('manufacturers_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
	  
	  $collection = Mage::getModel('manufacturers/manufacturers')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
	  
  }

  protected function _prepareColumns()
  {
      $this->addColumn('manufacturers_id', array(
          'header'    => Mage::helper('manufacturers')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'manufacturers_id',
      ));
	  
	  $this->addColumn('logo', array(
          'header'    => Mage::helper('manufacturers')->__('Logo'),
          'align'     =>'center',
		  'type'      => 'text',
		  'width'     => '100px',
		  'filter'    => false,
		  'sortable'      =>  false,
		  'renderer'  => 'FME_Manufacturers_Block_Adminhtml_Manufacturers_Renderer_Gridlogo',
       ));

      $this->addColumn('m_name', array(
          'header'    => Mage::helper('manufacturers')->__('Name'),
          'align'     =>'left',
          'index'     => 'm_name',
      ));
	  
	   $this->addColumn('identifier', array(
          'header'    => Mage::helper('manufacturers')->__('SEO Identifier'),
		  'width'     => '150px',
          'align'     => 'left',
          'index'     => 'identifier',
      ));
	  
	  $this->addColumn('products_count', array(
            'type'      => 'text',
            'header'    => $this->__('Products'),
            'width'     => '100px',
            'align'     => 'right',
			'filter'    => false,
			'sortable'      =>  false,
			'renderer'  => 'FME_Manufacturers_Block_Adminhtml_Manufacturers_Renderer_Productcount',
        ));

	  	  
	  $this->addColumn('m_website', array(
          'header'    => Mage::helper('manufacturers')->__('Website'),
          'align'     =>'left',
          'index'     => 'm_website',
      ));
	  
	  if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        =>  Mage::helper('manufacturers')->__('Store View'),
                'index'         =>  'store_id',
                'type'          =>  'store',
                'store_all'     =>  true,
                'store_view'    =>  true,
				'width'     => '150px',
                'sortable'      =>  false,
                'filter_condition_callback' =>  array($this, '_filterStoreCondition'),
            ));
       }
	
      $this->addColumn('status', array(
          'header'    => Mage::helper('manufacturers')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
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
	
	 /*
     * Article count filter callback
     * @see Mage_Adminhtml_Block_Widget_Grid::_addColumnFilterToCollection()
     */
    protected function _filterProductsCount($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue()) return;

        if(isset($value['from']) && isset($value['to']))
            $collection->getSelect()->having('products_count BETWEEN \''.
                addslashes($value['from']).'\' AND \''.addslashes($value['to']).'\'');
        elseif(isset($value['from']))
            $collection->getSelect()->having('products_count >=\''.addslashes($value['from']).'\'');
        elseif(isset($value['to']))
            $collection->getSelect()->having('products_count <=\''.addslashes($value['to']).'\'');
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