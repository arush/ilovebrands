<?php
/*
 *  Created on May 3, 2011
 *  Author Ivan Proskuryakov - volgodark@gmail.com - Magazento.com
 *  Copyright Proskuryakov Ivan. Magazento.com © 2011. All Rights Reserved.
 *  Single Use, Limited Licence and Single Use No Resale Licence ["Single Use"]
 */
?>
<?php
class Magazento_Homepage_Block_Toprated extends Mage_Catalog_Block_Product_Abstract {


	protected function _construct() {
		parent::_construct();
		$this->addData(array(
			'cache_lifetime' => 86400,
			'cache_tags' => array('magazentohomepage_home_toprated'),
		));
	}

        public function getTopRatedProduct() {
            $limit = $this->getModel()->getTopratedCount();
            $_products = Mage::getModel('catalog/product')->getCollection();
            $_products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
            $_products->addAttributeToSelect('*')->addStoreFilter();
            $_rating = array();
            foreach($_products as $_product) {
                $storeId = Mage::app()->getStore()->getId();
                $_productRating = Mage::getModel('review/review_summary')
                                    ->setStoreId($storeId)
                                    ->load($_product->getId());
                $_rating[] = array(
                             'rating' => $_productRating['rating_summary'],
                             'product' => $_product
                            );
            }
            arsort($_rating);
            return array_slice($_rating, 0, $limit);
        }

    public function getTitle() {
        return Mage::getStoreConfig('homepage/toprated/title') ;
    }
    public function getModel() {
        return Mage::getModel('homepage/Data');
    }

}
