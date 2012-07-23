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
	
	class GoMage_Navigation_Block_Product_List extends Mage_Catalog_Block_Product_List{
		
	    protected $_procartproductlist = null;
	    
		public function getAddToCompareUrl($product){
	        return $this->helper('gomage_navigation/compare')->getAddUrl($product);
	    }
	    
    	public function getAddToCartUrl($product, $additional = array()){
        
        	$_modules = Mage::getConfig()->getNode('modules')->children();       	   	   
    	    $_modulesArray = (array)$_modules;	   
    	    if(isset($_modulesArray['GoMage_Procart']) && $_modulesArray['GoMage_Procart']->is('active')){
                if (Mage::helper('gomage_procart')->isProCartEnable() && Mage::getStoreConfig('gomage_procart/qty_settings/category_page'))
                    $additional['_query']['gpc_prod_id'] = $product->getId();        
    	    }    
            return parent::getAddToCartUrl($product, $additional);
            
        }   
    
        public function getProcartProductList(){
            
            $_modules = Mage::getConfig()->getNode('modules')->children();       	   	   
    	    $_modulesArray = (array)$_modules;	   
    	    if(isset($_modulesArray['GoMage_Procart']) && $_modulesArray['GoMage_Procart']->is('active')){
                if (!$this->_procartproductlist){             
                     $this->_procartproductlist = array();
                     $helper = Mage::helper('gomage_procart');
                     
                     foreach ($this->getLoadedProductCollection() as $_product){                 
                         $product = Mage::getModel('catalog/product')->load($_product->getId());                 
                         $this->_procartproductlist[$product->getId()] = $helper->getProcartProductData($product);
                     }
                     
                }
                
                return Mage::helper('core')->jsonEncode($this->_procartproductlist);          
    	    }    
        }
        
		public function getToolbarHtml()
	    {
	    	$toolbar = $this->getChild('toolbar');
	        return $toolbar->toHtml(); 
	    }
	    
    }