<?php
/*
 *  Created on May 3, 2011
 *  Author Ivan Proskuryakov - volgodark@gmail.com - Magazento.com
 *  Copyright Proskuryakov Ivan. Magazento.com © 2011. All Rights Reserved.
 *  Single Use, Limited Licence and Single Use No Resale Licence ["Single Use"]
 */
?>
<?php

class Magazento_Homepage_Block_New extends Mage_Catalog_Block_Product_Abstract {


	protected function _construct() {
		parent::_construct();
		$this->addData(array(
			'cache_lifetime' => 86400,
			'cache_tags' => array('magazentohomepage_home_new'),
		));

	}
        
        protected function _beforeToHtml() {

            $collection = Mage::getResourceModel('catalog/product_collection');
            $from = Mage::getStoreConfig('homepage/new/from');
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $newDate=$this->getModel()->getSellDate($this->getModel()->getHomepageDaysLimit());
            $collection = $this->_addProductAttributesAndPrices($collection)
                        ->addStoreFilter()
                        ->addAttributeToFilter(array(
                            array('attribute'=>'created_at','from'=>$from,'to'=>$newDate['todaydate'])
                        ))
                        ->setPageSize($this->getModel()->getNewCount())
                        ->setCurPage(1)
                        ;

            $this->setProductCollection($collection);
	}

    public function getTitle() {
        return Mage::getStoreConfig('homepage/new/title') ;
    }
    
    public function getModel() {
        return Mage::getModel('homepage/Data');
    }

}














//				->setDateRange($sellDate['startdate'], $sellDate['todaydate'])
//				->addAttributeToFilter('is_salable')
//				->addAttributeToFilter('visibility', array('in' => array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)))
//				->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
//                                ->addSaleableFilterToCollection()
//                                ->addInStockFilterToCollection()
//                                ->addUrlRewrite()
////                                ->addCategoryFilter($currentCategory)
//                                ->setPageSize($this->getModel()->getHomepageProductsLimit())
//                                ->setCurPage(1)
//				->addOrderedQty()
//				->setOrder('ordered_qty', 'desc');
//

//			->addAttributeToSelect(array('entity_id', 'name', 'price', 'small_image', 'short_description', 'description', 'type_id', 'status'))
//			->addOrderedQty()
//			->setStoreId($storeId)
//			->addStoreFilter($storeId)
////			->addCategoryFilter($currentCategory)
//			->setOrder('ordered_qty', 'desc')
//                        ->setPageSize($this->getModel()->getHomepageProductsLimit())
//                        ->setCurPage(1);
//
//                $collection= array();
//                foreach ($rawcollection as $product) {
////                    $addproduct = $product->getData('is_salable');
//                    $collection[]=$product->getData();
//                }
//                var_dump($rawcollection);
////
//
//                exit();