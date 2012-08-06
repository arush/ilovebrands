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
 
class FME_Manufacturers_Block_Adminhtml_Manufacturers extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_manufacturers';
    $this->_blockGroup = 'manufacturers';
    $this->_headerText = Mage::helper('manufacturers')->__('Manufacturers Manager');
    $this->_addButtonLabel = Mage::helper('manufacturers')->__('Add Manufacturer');
    parent::__construct();
  }
}