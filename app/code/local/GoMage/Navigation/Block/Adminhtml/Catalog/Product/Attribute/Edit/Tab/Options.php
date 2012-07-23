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
 * @since        Class available since Release 1.0
 */
	
class GoMage_Navigation_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options{

	public function __construct(){
        parent::__construct();

        $this->setTemplate('gomage/navigation/product/attribute/options.phtml');
    }

	public function getOptionValues(){

		$values = parent::getOptionValues();

		if($values){

			$images = $this->getAttributeObject()->getOptionImages();

			foreach($values as $value){

				if(isset($images[$value['id']])){

					$value->setImageInfo(array($images[$value['id']]));

				}

			}

		}

		return $values;

	}

	public function getPopupTextValues()
    {
        $values = array();
        $storeLabels = array();
        
        $attribute_id = $this->getAttributeObject()->getId();
        
        $attribute_stores = Mage::getModel('gomage_navigation/attribute_store')	
									->getCollection()							
									->addFieldToFilter('attribute_id', $attribute_id)	            					
	            					->load();
	            					
	    foreach ($attribute_stores as $attribute_store){
	    	$storeLabels[$attribute_store->getData('store_id')] = $attribute_store->getData('popup_text'); 
	    }        					

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }
        return $values;
    }

}