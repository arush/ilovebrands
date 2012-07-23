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
	
	class GoMage_Navigation_Helper_Compare extends Mage_Catalog_Helper_Product_Compare{
		
		public function getEncodedUrl($url=null)
	    {
	        if (!$url) {
	            $url = $this->getCurrentUrl();
	        }
	        
	        $url = str_replace('ajax=1&', '', $url);
	        $url = str_replace('ajax=1', '', $url);
	        
	        return $this->urlEncode($url);
	    }
	    
	}