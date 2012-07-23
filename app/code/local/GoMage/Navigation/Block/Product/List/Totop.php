<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 3.0
 */

class GoMage_Navigation_Block_Product_List_Totop extends Mage_Core_Block_Template
{
	public function __construct()
    {
        parent::__construct();
        if ($this->showToTopButton()){
        	$this->setTemplate('gomage/navigation/catalog/product/list/back_to_top.phtml');
        }                               
    } 
	 
	public function showToTopButton(){
		return Mage::getStoreConfig('gomage_navigation/general/back_to_top');
	}
	                    
}
