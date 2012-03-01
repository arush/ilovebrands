<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Manage_Storefront_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $collection = null;
    protected $columnsAreSet = false;
    
    public function __construct()
    {
        parent::__construct ();
        $this->setId ( 'storefrontGrid' );
        $this->setTemplate('rewardsinstore/storefront/grid.phtml');
        $this->_blockGroup = 'rewardsinstore';
        $this->setDefaultSort ( 'rewards_storefront_id' );
        $this->setDefaultDir ( 'DESC' );
        $this->setSaveParametersInSession ( true );
    }
    
    protected function _getStore()
    {
        $storeId = ( int ) $this->getRequest ()->getParam ( 'store', 0 );
        return Mage::app ()->getStore ( $storeId );
    }
    
    protected function _prepareCollection()
    {
        if ($this->collection == null) {
            $this->collection = Mage::getModel ( 'rewardsinstore/storefront' )->getCollection ();
        }
        
        $store = $this->_getStore ();
        if ($store->getId ()) {
            $this->collection->addStoreFilter ( $store );
        }
        
        $this->collection->selectWebsiteName( 'website_name' );
        
        $this->setCollection ( $this->collection );
        
        return parent::_prepareCollection ();
    }
    
    protected function _prepareColumns()
    {
        if ($this->columnsAreSet) {
            return parent::_prepareColumns ();
        } else {
            $this->columnsAreSet = true;
        }
        
        $this->addColumn('storefront_id', array(
            'header'        => Mage::helper('rewards')->__('ID'),
            'align'         =>'left',
            'width'         =>'20px',
            'index'         => 'storefront_id',
        ));
        
        $this->addColumn('name', array(
          'header' => Mage::helper('rewards')->__('Storefront Name'), 
          'align' => 'left', 
          'index' => 'name' 
        ));
        
        $this->addColumn('description', array(
          'header' => Mage::helper('rewardsinstore')->__('Address'), 
          'align' => 'left', 
          'index' => 'description',
          'renderer'  => 'rewardsinstore/manage_storefront_grid_address' 
        ));
        
        // TODO: These are removed because they clutter the grid and make it hard to read.
        // May enable in the future if sorting becomes a priority for merchants
//        $this->addColumn('street', array(
//          'header' => Mage::helper('rewardsinstore')->__('Street'), 
//          'align' => 'left', 
//          'index' => 'street' 
//        ));
//        
//        $this->addColumn('city', array(
//          'header' => Mage::helper('rewardsinstore')->__('City'), 
//          'align' => 'left', 
//          'width' => '100px', 
//          'index' => 'city' 
//        ));
//        
//        $this->addColumn('region', array(
//          'header' => Mage::helper('rewardsinstore')->__('State/Province'), 
//          'align' => 'left', 
//          'width' => '100px', 
//          'index' => 'city',
//          'renderer'  => 'rewardsinstore/manage_storefront_grid_region'
//        ));
//        
//        $this->addColumn('country', array(
//          'header' => Mage::helper('rewardsinstore')->__('Country'), 
//          'align' => 'left', 
//          'width' => '100px', 
//          'index' => 'city' ,
//          'renderer'  => 'rewardsinstore/manage_storefront_grid_country'
//        ));
        
        $this->addColumn('storefront_website', array(
          'header' => Mage::helper('rewardsinstore')->__('Website'), 
          'align' => 'left', 
          'width' => '100px', 
          'index' => 'website_name' 
        ));
        
        $this->addColumn('storefront_code', array(
          'header' => Mage::helper('rewardsinstore')->__('Storefront Code'), 
          'align' => 'left', 
          'width' => '50px', 
          'index' => 'code' 
        ));
        
        $this->addColumn('is_active', array(
            'header'    => Mage::helper('rewardsinstore')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        
        $this->addColumn('action', array(
          'header' => Mage::helper('rewards')->__('Action'), 
          'width' => '50', 
          'align' => 'center',
          'type' => 'action', 
          'getter' => 'getId', 
          'actions' => array (
              array (
                  'caption' => Mage::helper('rewards')->__('Edit'), 
                  'url' => array (
                      'base' => '*/*/edit'
                  ), 
                  'field' => 'id' ) 
              ), 
              'filter' => false, 
              'sortable' => false, 
              'index' => 'stores', 
              'is_system' => true 
          ));
        
        return parent::_prepareColumns ();
    }
    
    protected function _prepareMassaction()
    {
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl ( '*/*/edit', array ('id' => $row->getId () ) );
    }
}
