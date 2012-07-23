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
	
class GoMage_Navigation_Helper_Data extends Mage_Core_Helper_Abstract{
    
    	
    public function getConfigData($node){
		return Mage::getStoreConfig('gomage_navigation/'.$node);
	}
	
	public function getAllStoreDomains(){
		
		$domains = array();
    	
    	foreach (Mage::app()->getWebsites() as $website) {
    		
    		$url = $website->getConfig('web/unsecure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    		$url = $website->getConfig('web/secure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    	}
    	
    	return array_unique($domains);
    	
		
	}
	
	public function getAvailabelWebsites(){
		return $this->_w();
	}
	
	public function getAvailavelWebsites(){
		return $this->_w();
	}
	
	protected function _w(){
    
        if(!Mage::getStoreConfig('gomage_activation/advancednavigation/installed') || 
           (intval(Mage::getStoreConfig('gomage_activation/advancednavigation/count')) > 10))
		{
			return array();
		}
		            		
		$time_to_update = 60*60*24*15;
		
		$r = Mage::getStoreConfig('gomage_activation/advancednavigation/ar');
		$t = Mage::getStoreConfig('gomage_activation/advancednavigation/time');
		$s = Mage::getStoreConfig('gomage_activation/advancednavigation/websites');
		
		$last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));
		
		$allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
		$allsites = array_diff($allsites, array(""));
			
		if(($last_check+$time_to_update) < time()){
			$this->a(Mage::getStoreConfig('gomage_activation/advancednavigation/key'), 
			         intval(Mage::getStoreConfig('gomage_activation/advancednavigation/count')),
			         implode(',', $allsites));
		}
		
		return $allsites;
		
	}
	
	public function a($k, $c = 0, $s = ''){
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key='.urlencode($k).'&sku=advanced-navigation&domains='.urlencode(implode(',', $this->getAllStoreDomains())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $content = curl_exec($ch);
        
        $r	= Zend_Json::decode($content);
        $e = Mage::helper('core');
        if(empty($r)){
        	
        	$value1 = Mage::getStoreConfig('gomage_activation/advancednavigation/ar');
        	
	        $groups = array(
	        	'advancednavigation'=>array(
	        		'fields'=>array(
	        			'ar'=>array(
	        				'value'=>$value1
	        			),
	        			'websites'=>array(
	        				'value'=>(string)Mage::getStoreConfig('gomage_activation/advancednavigation/websites')
	        			),
	        			'time'=>array(
	        				'value'=>(string)$e->encrypt($value1.(time()-(60*60*24*15-1800)).$value1)
	        			),
	        			'count'=>array(
	        				'value'=>$c+1)
	        		)
	        	)
        	);
        	
	        Mage::getModel('adminhtml/config_data')
	                ->setSection('gomage_activation')
	                ->setGroups($groups)
	                ->save();
        	
	        Mage::getConfig()->reinit();
            Mage::app()->reinitStores();        
	                
        	return;
        }
        
        $value1 = '';
        $value2 = '';
        
        
        
        if(isset($r['d']) && isset($r['c'])){
    		$value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));
        
        
        if (!$s) $s = Mage::getStoreConfig('gomage_activation/advancednavigation/websites');
        
        $s = array_slice(explode(',', $s), 0, $r['c']);
        
        $value2 = $e->encrypt($value1.implode(',', $s).$value1);
        
        }
        $groups = array(
        	'advancednavigation'=>array(
        		'fields'=>array(
        			'ar'=>array(
        				'value'=>$value1
        			),
        			'websites'=>array(
        				'value'=>(string)$value2
        			),
        			'time'=>array(
        				'value'=>(string)$e->encrypt($value1.time().$value1)
        			),
        			'installed'=>array(
        				'value'=>1
        			),
        			'count'=>array(
        				'value'=>0)
        			
        		)
        	)
        );
        
        Mage::getModel('adminhtml/config_data')
                ->setSection('gomage_activation')
                ->setGroups($groups)
                ->save();
                
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();        
        
	}
	
	public function ga(){
		return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/advancednavigation/ar'))));
	}
	
	public function isGomageNavigation(){
	     return in_array(Mage::app()->getStore()->getWebsiteId(), $this->getAvailavelWebsites()) &&
	            Mage::getStoreConfigFlag('gomage_navigation/general/mode'); 	         	     
	}
	
	public function isGomageNavigationAjax(){
	     return $this->isGomageNavigation() &&                 
                ((Mage::registry('current_category') && Mage::registry('current_category')->getisAnchor()) ||
                (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch' &&
                 Mage::app()->getFrontController()->getRequest()->getControllerName() != 'advanced'));
	}
	
	public function formatColor($value){
	    if ($value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value)){
	       $value = '#' . $value; 	        
	    }
	    return $value;
	}
	
	public function isFrendlyUrl(){
		return $this->isGomageNavigation() && Mage::getStoreConfigFlag('gomage_navigation/general/frendlyurl');
	}
	
	public function getFilterUrl($route = '', $params = array()){
		
		if (!$this->isFrendlyUrl()){
            return Mage::getUrl($route, $params);
        }

        $model = Mage::getModel('core/url');
        $request_query = $model->getRequest()->getQuery();
        $attr = Mage::registry('gan_filter_attributes');

        foreach($model->getRequest()->getQuery() as $param => $value){
        	if ($param == 'cat'){
            	$values = explode(',', $value);
            	$prepare_values = array();
            	foreach($values as $_value){
            		$category = Mage::getModel('catalog/category')->load($_value);
            		if ($category && $category->getId()){
            			$prepare_values[] = $category->getData('url_key');
            		}
            	}
            	$model->getRequest()->setQuery($param, implode(',', $prepare_values));
            }elseif (isset($attr[$param]) && !in_array($attr[$param]['type'], array('price', 'decimal'))){	
            	$values = explode(',', $value);
            	$prepare_values = array();
            	foreach($values as $_value){                		
            		foreach($attr[$param]['options'] as $_k => $_v){
            			if ($_v == $_value){
            				$prepare_values[] = $_k;
            				break;
            			}
            		}
            	}            		
                $model->getRequest()->setQuery($param, implode(',', $prepare_values));                
            }
        }
                
        foreach ($params['_query'] as $param => $value){
        	if ($value){
	        	if ($param == 'cat'){
	            	$values = explode(',', $value);
	            	$prepare_values = array();
	            	foreach($values as $_value){
	            		$category = Mage::getModel('catalog/category')->load($_value);
	            		if ($category && $category->getId()){
	            			$prepare_values[] = $category->getData('url_key');
	            		}
	            	}
	            	$params['_query'][$param] = implode(',', $prepare_values);
	            }elseif (isset($attr[$param]) && !in_array($attr[$param]['type'], array('price', 'decimal'))){	
	            	$values = explode(',', $value);
	            	$prepare_values = array();
	            	foreach($values as $_value){            			
		            	foreach($attr[$param]['options'] as $_k => $_v){
	            			if ($_v == $_value){
	            				$prepare_values[] = $_k;
	            				break;
	            			}
	            		}            		
	            	}            		
	                $params['_query'][$param] = implode(',', $prepare_values);                
	            }
        	}
        }
        
        $url = $model->getUrl($route, $params);

        foreach($request_query as $param => $value){
        	$model->getRequest()->setQuery($param, $value);
        }
        
        return $url; 
        
	}
	
 	public function formatUrlValue($value){    	
        $value = preg_replace('#[^0-9a-z]+#i', '_', Mage::helper('catalog/product_url')->format($value));
        $value = strtolower($value);
        $value = trim($value, '-');

        return $value;
    }
				
}

