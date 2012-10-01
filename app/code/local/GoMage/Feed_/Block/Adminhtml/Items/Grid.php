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

class GoMage_Feed_Block_Adminhtml_Items_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
    public function __construct(){
    	
        parent::__construct();
        $this->setId('gomagefeedsGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        
    }
    
    protected function _prepareCollection(){
    	
        $collection = Mage::getModel('gomage_feed/item')->getCollection();
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
        
        $this->addColumn('filename', array(
	          'header'    => $this->__('Access Url'),
	          'align'     =>'left',
	          'index'     => 'filename',
	    	  'renderer'  => 'GoMage_Feed_Block_Adminhtml_Items_Grid_Renderer_AccessUrl'
	      ));
	    $this->addColumn('last_generated', array(
            'header'    =>  $this->__('Last Generated'),
            'align'     =>  'left',
            'index'     =>  'generated_at',
            'type'		=> 'datetime',
            'renderer'  => 'GoMage_Feed_Block_Adminhtml_Items_Grid_Renderer_Datetime'
        ));
        
        $this->addColumn('uploaded_at', array(
            'header'    =>  $this->__('Last Uploaded'),
            'align'     =>  'left',
            'index'     =>  'uploaded_at',
            'type'		=> 'datetime',
            'renderer'  => 'GoMage_Feed_Block_Adminhtml_Items_Grid_Renderer_Datetime'
        ));
        
        
        $this->addColumn('store_id', array(
            'header'    =>  $this->__('Store View'),
            'align'     =>  'left',
            'index'     =>  'store_id',
            'type'      =>  'store',            
        ));
        /*
        $this->addColumn('type', array(
            'header'    =>  $this->__('Content Type'),
            'align'     =>  'left',
            'index'     =>  'type',
        ));
        */
        $this->addColumn('ftp_active', array(
            'header'    =>  $this->__('FTP Status'),
            'align'     =>  'left',
            'index'     =>  'ftp_active',
            'type'      => 'options',
            'options'   => array(
            	0=>$this->__('Disabled'),
            	1=>$this->__('Enabled'),
            ),
            
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
        
        
        $this->getMassactionBlock()->addItem('generate', array(
            'label'     =>  $this->__('Generate File(s)'),
            'url'       =>  $this->getUrl('*/*/massGenerate'),
            'confirm'   =>  $this->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('upload', array(
            'label'     =>  $this->__('Upload File(s)'),
            'url'       =>  $this->getUrl('*/*/massUpload'),
            'confirm'   =>  $this->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('delete', array(
            'label'     =>  $this->__('Delete Feed(s)'),
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