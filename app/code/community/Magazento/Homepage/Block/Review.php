<?php
/*
 *  Created on May 3, 2011
 *  Author Ivan Proskuryakov - volgodark@gmail.com - Magazento.com
 *  Copyright Proskuryakov Ivan. Magazento.com © 2011. All Rights Reserved.
 *  Single Use, Limited Licence and Single Use No Resale Licence ["Single Use"]
 */
?>
<?php

class Magazento_Homepage_Block_Review extends Mage_Catalog_Block_Product_Abstract {

    protected function _construct() {
            parent::_construct();
            $this->addData(array(
                    'cache_lifetime' => 86400,
                    'cache_tags' => array('magazentohomepage_home_review'),
            ));
    }


    function getReviewsData(){
        $pending  = 2;  
        $declined = 3;  
        $reviews = $this->getReviews();
        $count = 0;  
        foreach ($reviews as $review){  
          if($review['status_id'] != $pending || $review['status_id'] != $declined){
//              var_dump($review);
//              exit();
            $vals = $this->getRatingValues($review);
//            $allReviews[$count]['product_sku'] = $_product->getSku();
//            $allReviews[$count]['status_id'] = $review['status_id'];
            $allReviews[$count]['review_url'] = $review->getReviewUrl($review->getId());
            $num = $count +1;
            $allReviews[$count]['title'] = $num.'. '.$review['title'];
            $allReviews[$count]['detail'] = $review['detail'];  
            $allReviews[$count]['nickname'] = $review['nickname'];  
//            $allReviews[$count]['customer_id'] = $review['customer_id'];  
            $allReviews[$count]['ratings'] = $vals;  
            $count++;      
          }  
        }  

        return $allReviews;
    }
    function getRatingValues(Mage_Review_Model_Review $review){
      $avg = 0;
      if( count($review->getRatingVotes()) ) {
        $ratings = array();
        $c = 0;
        foreach( $review->getRatingVotes() as $rating ) {
          $type = $rating->getRatingCode();
          $pcnt = $rating->getPercent();
        if($type){
          $val[$c][$type] = $pcnt;

        }
        $ratings[] = $rating->getPercent();
        }
        $c++;
        $avg = array_sum($ratings)/count($ratings);
      }
      return $val;
    }
    
    function getReviews() {
      $reviews = Mage::getModel('review/review')->getResourceCollection();
      $reviews->addStoreFilter( Mage::app()->getStore()->getId() )
              ->addStatusFilter( Mage_Review_Model_Review::STATUS_APPROVED )
              ->setDateOrder()
              ->addRateVotes()
              ->load();
      $return=array();
      $i=0;
      foreach ( $reviews as $review) {
          $return[] = $review;
          $i++; if ($i == $this->getModel()->getReviewsCount()) break;
      }
      return $return;
    }

    public function getTitle() {
        return Mage::getStoreConfig('homepage/review/title') ;
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