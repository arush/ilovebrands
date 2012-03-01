<?php

/**
 * Pod1 Shutl extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @copyright  Copyright (c) 2010 Pod1 (http://www.pod1.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shutl Form Block
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */

class Pod1_Shutl_Block_Form extends Mage_Core_Block_Template {

	public $today;
	public $start_date;
	public $end_date;
	public $last_selectable_date;
	public $current_date;
	public $selected_date;
	
    public function __construct() {
        parent::__construct();
		$_helper					= Mage::helper('shutl');
		$today						= date('Y-m-d-M-j-N');
		$todayParts					= explode('-', $today);
		$daysAllowed				= $_helper->getDeliveryWindow();
		$startDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' -'.($todayParts[5]-1).' day'));
	
		$lastSelectableDate			= date('Y-m-d-M-j-N', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$lastSelectableDateParts	= explode('-', $lastSelectableDate);
	
		$endDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$endDate					= date('Y-m-d-M-j-N', strtotime($endDate . ' +'.(7-($lastSelectableDateParts[5])).' day'));			

		$this->today				= $today;
		$this->start_date			= $startDate;
		$this->end_date				= $endDate;
		$this->last_selectable_date = $lastSelectableDate;
		$this->current_date			= date('Y-m-d-M-j-N', strtotime($startDate));
		$this->selected_date		= $_helper->getSelectedDate();

    }
    
    
    public function getToday() {
    	return $this->today;
    }
    
    public function getStartDate() {
    	return $this->start_date;
    }
    
    public function getEndDate() {
    	return $this->end_date;
    }
    public function getLastSelectableDate() {
    	return $this->last_selectable_date;
    }
    public function getCurrentDate() {
		return $this->current_date;
    }
    public function getSelectedDate() {
		return $this->selected_date;
    }
}