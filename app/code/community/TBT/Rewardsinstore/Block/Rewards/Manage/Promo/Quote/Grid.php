<?php

class TBT_Rewardsinstore_Block_Rewards_Manage_Promo_Quote_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct($ruleTypeId) {
		parent::__construct ();
		$this->setId ( 'promo_quote_grid' );
		$this->setDefaultSort ( 'sort_order' );
		$this->setDefaultDir ( 'ASC' );
		$this->setRuleTypeId ( $ruleTypeId );
		$this->setSaveParametersInSession ( true );
	}
	
	/**
	 * Fetches the rule type helper;
	 * @return TBT_Rewards_Helper_Rule_Type
	 */
	public function _getTypeHelper() {
		return Mage::helper ( 'rewards/rule_type' );
	}
	
	protected function _prepareCollection() {
		$catalogruleActionsSingleton = Mage::getSingleton ( 'rewards/salesrule_actions' );
		$allowedActions = array ();
		if ($this->_getTypeHelper ()->isDistribution ( $this->getRuleTypeId () )) { // is a distribution
			$allowedActions = $catalogruleActionsSingleton->getDistributionActions ();
		} else {
			$allowedActions = $catalogruleActionsSingleton->getRedemptionActions ();
		}
        
        $collection = Mage::getModel('rewards/salesrule_rule')->getResourceCollection()->addFieldToFilter("points_action", array('IN' => $allowedActions));
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$this->addColumn ( 'rule_id', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'ID' ), 'align' => 'right', 'width' => '50px', 'index' => 'rule_id' ) );
		
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Rule Name' ), 'align' => 'left', 'index' => 'name' ) );
		
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.0.0' )) {
			$this->addColumn ( 'coupon_code', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Coupon Code' ), 'align' => 'left', 'width' => '150px', 'index' => 'code' ) );
		} else {
			$this->addColumn ( 'coupon_code', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Coupon Code' ), 'align' => 'left', 'width' => '150px', 'index' => 'coupon_code' ) );
		}
		
		$this->addColumn ( 'from_date', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Date Start' ), 'align' => 'left', 'width' => '120px', 'type' => 'date', 'index' => 'from_date' ) );
		
		$this->addColumn ( 'to_date', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Date Expire' ), 'align' => 'left', 'width' => '120px', 'type' => 'date', 'default' => '--', 'index' => 'to_date' ) );
		
		$this->addColumn ( 'is_active', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Status' ), 'align' => 'left', 'width' => '80px', 'index' => 'is_active', 'type' => 'options', 'options' => array (1 => 'Active', 0 => 'Inactive' ) ) );
		
		$this->addColumn ( 'sort_order', array ('header' => Mage::helper ( 'salesrule' )->__ ( 'Priority' ), 'align' => 'right', 'index' => 'sort_order' ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array ('id' => $row->getRuleId (), 'type' => $this->getRuleTypeId () ) );
	}

}
