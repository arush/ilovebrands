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

class GoMage_Navigation_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    
    public function getRemoveUrl($ajax = false)
    {    	
        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue($this->getValue()));
        $params['_nosid']       = true;
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = false;
        
        $params['_query']['ajax'] = null;
        
        if($ajax){
        	
        	$params['_query']['ajax'] = true;
        	
        	
        }        
        
        return Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', $params);
    }
    
    public function getUrl($ajax = false)
    {
    	
    	if($this->hasData('url')){
    		
    		return $this->getData('url');
    		
    	}
    	
    	$query = array(
	            $this->getFilter()->getRequestVar()=>$this->getValue(),
	            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
	        );
	    
	    $query['ajax'] = null;
	    
    	if($ajax){
        	
        	$query['ajax'] = 1;
        	
        }
        
        return Mage::helper('gomage_navigation')->getFilterUrl('*/*/*', array('_current'=>true, '_nosid'=>true, '_use_rewrite'=>true, '_query'=>$query, '_escape'=>false)); 
        
    }
    
}
