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
 * Shutl Tracker front controller
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @author     Steve van de Heuvel <magento-dev@pod1.com>
 */
 
class Pod1_Shutl_AboutController extends Mage_Core_Controller_Front_Action {


	public function indexAction() {
		if (!Mage::getSingleton('customer/session')->getCustomer()) {
			print_r('0');
		}
        
		$_helper					= Mage::helper('shutl');
		$today						= date('Y-m-d-M-j-N');
		$todayParts					= explode('-', $today);
		$daysAllowed				= $_helper->getDeliveryWindow();
		$startDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' -'.($todayParts[5]-1).' day'));
	
		$lastSelectableDate			= date('Y-m-d-M-j-N', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$lastSelectableDateParts	= explode('-', $lastSelectableDate);
	
		$endDate					= date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$daysAllowed.' day'));
		$endDate					= date('Y-m-d-M-j-N', strtotime($endDate . ' +'.(7-($lastSelectableDateParts[5])).' day'));						

        $this->loadLayout();
		$this->getLayout()->getBlock('form')->setData(
			array(
				'today'			=> $today,
				'start_date'	=> $startDate,
				'end_date'		=> $endDate,
				'last_selectable_date' => $lastSelectableDate,
				'current_date'	=> date('Y-m-d-M-j-N', strtotime($startDate)),
				'selected_date'	=> $_helper->getSelectedDate()
				
			)
		);        	
        
        $this->renderLayout();

	}
}