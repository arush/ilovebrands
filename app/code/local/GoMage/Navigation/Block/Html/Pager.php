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

class GoMage_Navigation_Block_Html_Pager extends Mage_Page_Block_Html_Pager
{        
    protected function _construct()
    {
        parent::_construct();        
        if($this->isAjaxPager()){
            $this->setTemplate('gomage/navigation/html/pager.phtml');
        }else{
            $this->setTemplate('page/html/pager.phtml');
        }
    }
    
    public function getPagerUrl($params=array())
    {
        if($this->isAjaxPager()){    	
    	   $params['ajax'] = 1;    	
    	}else{
    	   $params['ajax'] = null; 
    	}    	
    	    	    	
        $urlParams = array();
        $urlParams['_nosid']    = true;
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        
        return Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', $urlParams);
    }
    
    public function isAjaxPager(){
        return Mage::helper('gomage_navigation')->isGomageNavigation() &&
               Mage::getStoreConfigFlag('gomage_navigation/general/pager') &&  
               ((Mage::registry('current_category') && Mage::registry('current_category')->getisAnchor()) ||
                (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch')); 
    }
    
}
