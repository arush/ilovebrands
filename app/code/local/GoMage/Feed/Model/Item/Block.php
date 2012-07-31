<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Feed_Model_Item_Block extends Varien_Object
{
	protected $content = '';
	protected $pattern = '/\\{\\{block\b(.*?)\\}\\}(.*)\\{\\{\\/block\\}\\}/is';
	protected $var_pattern = '/\\{\\{var:(.+?)(\s.*?)?\\}\\}/s';
	protected $increment_id = 0;
	protected $children = array();
	protected $feed;
	
	public function getFeed(){
		return $this->feed;
	}
	public function setFeed(GoMage_Feed_Model_Item $feed){
		$this->feed = $feed;
		return $this;
	}
	
	
    public function __construct($vars){
    	
    	extract($vars);
    	
    	if($feed instanceof GoMage_Feed_Model_Item){
    		
    		$this->feed	= $feed;
    		
    		$this->setDate(Date('Y-m-d H:i'));
    		
    		if(isset($content) && $content){
    			if(isset($is_xml) && $is_xml){
    				$this->content	= (string)$content;
    			}else{
    				
    				$content = str_replace('<', '|lquote|', $content);
					$content = str_replace('>', '|rquote|', $content);
					
					$content = preg_replace('/{{(block.*?)}}/is', '<$1>', $content);
					$content = preg_replace('/{{(\\/block)}}/is', '<$1>', $content);
					
    				$this->content	= $content;
    				
    			}
        		$this->parse();
        		
        	}
        
        }else{
        	throw new Exception('Feed must be instane of "GoMage_Feed_Model_Item"');
        }
    }
    
    protected function _parseChildBlocks(){
    	
    	
    	
    }
    
    
    
    protected function parse(){
    	
    	$match_block = array();
    	
    	
    	
    	$xml = new Varien_Simplexml_Config(sprintf('<xml><body>%s</body></xml>', $this->content));
    	
    	$root = $xml->getNode()->descend('body');
    	
    	$this->content = preg_replace('/<body\b.*?>(.*)<\\/body>/is', '$1', (string)$root->asXML());
    	
    	
    	foreach($root->children() as $nodeName=>$xml_block){
    	/*
    	while(preg_match($this->pattern, $this->content, $match_block)){
        	if(isset($match_block[2]) && ($content = trim($match_block[2]))){ /**/
        		
        		$wrapper = trim($xml_block->asXML());
        		
        		$content = trim(preg_replace('/<block\b.*?>(.*)<\\/block>/is', '\1', $wrapper));
        		
        		/*
        		
        		$attributes	= array();
        		
        		if(isset($match_block[1]) && $match_block[1]){
        			
        			$_attributes	= array();
        			
        			preg_match_all('/(.*?)\="(.*?)"/', $match_block[1], $_attributes);
        			
        			$attribute_count = count($_attributes[0]);
        			
        			for($i=0;$i<$attribute_count;$i++){
        				
        				if(isset($_attributes[1][$i]) && isset($_attributes[2][$i]) ){
        					
        					$attributes[trim($_attributes[1][$i])] = trim($_attributes[2][$i]);
        				
        				}
        				
        			}
        		
        		}
        		*/
        		$block = null;
        		
        		if(($type = $xml_block->getAttribute('type')) && ($modelClassName = Mage::getConfig()->getModelClassName('gomage_feed/item_block_'.strtolower($type)))){
        			
        			try{
	        			
	        			class_exists($modelClassName);
	        			
	        			$block = new $modelClassName(array('content'=>$content, 'feed'=>$this->feed, 'is_xml'=>1));
	        			
	        			if(false == ($block instanceof GoMage_Feed_Model_Item_Block)){
	        				
	        				$block = null;
	        				
	        			}
	        			
	        		}catch(Exception $e){
	        			$block = null;
	        		}
        			
        		}
	        	
	        	if(is_null($block)){
	        		
	        		$block = Mage::getModel('gomage_feed/item_block', array('content'=>$content, 'feed'=>$this->feed, 'is_xml'=>1));
	        		
	       		}
	       		
	       		$attrs = array();
	       		
	       		foreach($xml_block->attributes() as $key=>$value){
	       			$attrs[$key] = $value;
	       		}
	       		
	       		$block->addData($attrs);
	       		
	       		
	       		/*
	       		if(!empty($attributes)){
	       			
	       			$block->addData($attributes);
	       			
	       		}
	       		*/
	       		
	       		$this->children[$this->increment_id] = $block;
	        	
        		$this->content = str_replace($wrapper, sprintf('{{block:%d}}', $this->increment_id), $this->content);
        		
        		$this->increment_id++;
        		
        	/*}*/
        	
        }
    }
    public function getChildren(){
    	return $this->children;
    }
    public function getContent(){
    	return $this->content;
    }
    public function render(){
    	
    	foreach($this->children as $id=>$block){
    		$this->content = str_replace(sprintf('{{block:%d}}', $id), $block->render(), $this->content);
    	}
    	
    	$this->content = str_replace('|lquote|', '<', $this->content);
		$this->content = str_replace('|rquote|', '>', $this->content);
		
    	
    	return $this->setVars($this->content, null, (get_class($this) == 'GoMage_Feed_Model_Item_Block'));
    	
    }
    
    public function applyAttribute($attribute_name, $attribute_value, $value){
    	
    	
    	switch(trim($attribute_name)){
    		case 'format':
    			
    			switch(trim($attribute_value)){
    				case 'float':
    					
    					$value = number_format($value, 2, '.', '');
    					
    				break;
    				
    				case 'int':
    					
    					$value = intval($value);
    					
    				break;
    				
    				case 'striptags':
    					
    					$value = strip_tags($value);
    					$value = trim($value);
    					
    				break;
    				
    				case 'htmlspecialchars':    					
    					$encoding = mb_detect_encoding($value);
                        $value = mb_convert_encoding($value, "UTF-8", $encoding);				    				    				    				                      				    				        				    				              
                        $value = htmlentities($value, null, "UTF-8");    					
    				break;
    				
    				case 'htmlspecialchars_decode':
    					
    					$value = htmlspecialchars_decode($value);
    					
    				break;
    				
    				case 'delete_space':				    					

    				    $value = str_replace(" ", "", $value);
    					
    				break;
    				
    			}
    			
    		break;
    		
    		case 'limit':
    			if(intval($attribute_value) && $value){
    				$value = substr($value, 0, intval($attribute_value));
    			}
    		break;	
    		
    	}
    	
    	return $value;
    	
    }
    
    public function getAllVars($content = null){
    	
    	if(is_null($content)){
    		$content = $this->getContent();
    	}
    	
    	$vars = array();
    	$match = array();
    	
    	preg_match_all($this->var_pattern, $content, $match);
    	
    	if($var_num = count($match[0])){
	    	
	    	while($var_num--){
	    		$vars[] = $match[1][$var_num];
	    	}
			
		}
    	
    	return $vars;
    	
    }
    
    public function setVars($content = null, $dataObject = null, $clearVars = false){
    	
    	if(is_null($content)){
    		$content = $this->getContent();
    	}
    	if(is_null($dataObject)){
    		$dataObject = $this;
    	}
    	
    	$match = array();
    	
    	preg_match_all('/{{var:(.+?)(\s.*?)?}}/s', $content, $match);
    	
    	
    	if(!empty($match)){
    		
	    	if($var_num = count($match[0])){
	    	
		    	while($var_num--){
		    	
			    	$props = explode('.', $match[1][$var_num]);
			    	reset($props);
			    	
					$value = '';
					
					if($props_count = count($props)){
						
						try{

			    			$value = $dataObject->getData($props[0]);
			    			
			    			if($props_count > 1){
				    			
				    			for($i = 1; $i < $props_count;$i++){
				    				
				    				if($value instanceof Varien_Object){
				    					
				    					$value = $value->getData($props[$i]);
				    					
				    				}else{
				    					
				    					break;
				    					
				    				}
				    				
				    			}
			    			
			    			}
			    			
			    			$attributes = array();
					    	
					    	if($attributes_value = $match[2][$var_num]){
					    		
					    		preg_match_all('/(.*?)\="(.*?)"/s', $attributes_value, $attributes);
					    		
					    		foreach($attributes[1] as $i=>$attribute_name){
					    			
					    			$value = $this->applyAttribute($attribute_name, $attributes[2][$i], $value);
					    			
					    		}
					    		
					    	}
			    			
						}catch(Exception $e){
							$value = '';
						}
		    			
		    			
		    			if($value !== null || $clearVars == true){
		    				
		    				$content = str_replace($match[0][$var_num], strval($value), $content);
		    				
		    			}
			    	
			    	}
		    	}
	    	}
	    }
    	
    	return $content;
    }
    
}


