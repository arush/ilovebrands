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
class TBT_Rewardsinstore_Block_Manage_Earning_Cart_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct ($ruleTypeId)
    {
        parent::__construct();
        $this->setId('instore_cart_grid');
        $this->_blockGroup = 'rewardsinstore';
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setRuleTypeId ( $ruleTypeId );
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * Fetches the rule type helper;
     * @return TBT_Rewards_Helper_Rule_Type
     */
    public function _getTypeHelper ()
    {
        return Mage::helper('rewards/rule_type');
    }
    
    protected function _prepareCollection()
    {
        $catalogruleActionsSingleton = Mage::getSingleton ( 'rewards/salesrule_actions' );
        $allowedActions = array ();
        
        if ($this->_getTypeHelper()->isDistribution($this->getRuleTypeId())) { // is a distribution
            $allowedActions = $catalogruleActionsSingleton->getDistributionActions();
        } else {
            $allowedActions = $catalogruleActionsSingleton->getRedemptionActions();
        }
        
        $collection = Mage::getModel('rewardsinstore/cartrule')->getResourceCollection();
//            ->addFieldToFilter("points_action", array ('IN' => $allowedActions));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        if ($this->columnsAreSet) {
            return parent::_prepareColumns ();
        } else {
            $this->columnsAreSet = true;
        }
        
        $this->addColumn('rule_id', array(
            'header'    => Mage::helper('salesrule')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'rule_id',
        ));
        
        $this->addColumn('name', array(
            'header'    => Mage::helper('salesrule')->__('Rule Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));
        
        $this->addColumn('storefront_names', array(
            'header'    => Mage::helper('rewardsinstore')->__('Storefronts'),
            'align'     =>'left',
            'index'     => 'main_table.storefront_ids',
            'renderer'  => 'rewardsinstore/manage_grid_renderer_cartrule'
        ));
        
        $this->addColumn('from_date', array(
            'header'    => Mage::helper('salesrule')->__('Date Start'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'index'     => 'from_date',
        ));
        
        $this->addColumn('to_date', array(
            'header'    => Mage::helper('salesrule')->__('Date Expire'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'to_date',
        ));
        
        $this->addColumn('is_active', array(
            'header'    => Mage::helper('salesrule')->__('Status'),
            'align'     => 'left',
            'width'     => '50px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId() , 'type' => $this->getRuleTypeId()));
    }
}
