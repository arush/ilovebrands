<?php

	class Sonassi_Wordpress_IndexController extends Mage_Core_Controller_Front_Action
	{
		
    public function _initAction()
    {
        $this->loadLayout(array('default','blog_default'));
        return $this;
    }		
		
		public function defaultIndexAction() {
			exit("Default Index.");
		}
		
		public function noRouteAction($coreRoute = null) {
			$this->indexAction();
		}
		
		public function defaultNoRouteAction() {
			exit("No route");
		}
		
		public function indexAction()
		{			
			Mage::setIsDeveloperMode(true); 
			$this->_initAction();

			$_page = Mage::getSingleton('Sonassi_Wordpress_Model_Page');
			    
	    $this->getLayout()->getBlock('head')->setTitle(
	    		$_page->getTitle()
	    	)->setKeywords(
	    		
	    	);
	    	
	    $title = (string)Mage::getStoreConfig('system/store/name');	
	    $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');	
	    
			$crumbs = $_page->generateBreadcrumbs();
			
			foreach ($crumbs as $name=>$breadcrumb) {
				$breadcrumbsBlock->addCrumb($name, $breadcrumb);
				$title = $breadcrumb['label'].' '.Mage::getStoreConfig('catalog/seo/title_separator').' '.$title;
			}
	
				
			if ($meta = Mage::getModel('wordpress/page')->getMeta()) {
				foreach ($meta as $function=>$value) {
					$this->getLayout()->getBlock('head')->$function($value);
				}
			}      


     $this->renderLayout();
     
     #echo $this->getFullActionName();

		}
		
	}
