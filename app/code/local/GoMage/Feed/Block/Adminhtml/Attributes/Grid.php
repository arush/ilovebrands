<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Feed_Block_Adminhtml_Attributes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
    public function __construct(){
    	
        parent::__construct();
        $this->setId('gomagefeedsGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        
    }
    
    protected function _prepareCollection(){
    	
    	
    	
        $collection = Mage::getModel('gomage_feed/custom_attribute')->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
        
    }
    
    protected function _prepareColumns(){
    	
    	$this->addColumn('id', array(
            'header'    =>  $this->__('ID'),
            'align'     =>  'left',
            'index'     =>  'id',
            'type'  => 'number',
            'width' => '50px',
        ));
    	
        $this->addColumn('name', array(
            'header'    =>  $this->__('Name'),
            'align'     =>  'left',
            'index'     =>  'name',
        ));
        /*
        $this->addColumn('attribute_code', array(
	          'header'    => $this->__('Catalog Code'),
	          'align'     =>'left',
	          'index'     => 'attribute_code',
	    ));
        /**/
        $this->addColumn('code', array(
	          'header'    => $this->__('Dynamic Attribute Code'),
	          'align'     =>'left',
	          'index'     => 'code',
	    ));
        
        
        $this->addColumn('action', array(
            'header'    =>  $this->__('Action'),
            'width'     =>  '100',
            'type'      =>  'action',
            'getter'    =>  'getId',
            'actions'   =>  array(
                array(
                    'caption'   =>  $this->__('Edit'),
                    'url'       =>  array('base'=> '*/*/edit'),
                    'field'     =>  'id'
                )
            ),
            'filter'    =>  false,
            'sortable'  =>  false,
            'index'     =>  'stores',
            'is_system' =>  true,
        ));
        
        return parent::_prepareColumns();
        
    }
    
    protected function _prepareMassaction(){
        
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        
        $this->getMassactionBlock()->addItem('delete', array(
            'label'     =>  $this->__('Delete'),
            'url'       =>  $this->getUrl('*/*/massDelete'),
            'confirm'   =>  $this->__('Are you sure?')
        ));
        
        
        return $this;
        
    }
    
    
    protected function _afterLoadCollection(){
        
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
        
    }
    
    protected function _filterStoreCondition($collection, $column){
        
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        
        $this->getCollection()->addStoreFilter($value);
        
    }
    
    public function getRowUrl($row){
        
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        
    }
    
}