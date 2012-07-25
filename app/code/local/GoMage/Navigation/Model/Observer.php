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
	
	class GoMage_Navigation_Model_Observer{
		
		public function loadAttribute($event){
			
			$attribute		= $event->getAttribute();
			$attribute_id	= (int)$attribute->getAttributeId();
			
			$connection = Mage::getSingleton('core/resource')->getConnection('read');
			
			$table = Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute');
			
			$data = $connection->fetchRow("SELECT * FROM {$table} WHERE `attribute_id` = {$attribute_id};");
			
			$table = Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute_option');
			
			$option_images = array();
			$_option_images = $connection->fetchAll("SELECT * FROM {$table} WHERE `attribute_id` = {$attribute_id};");
			
			foreach($_option_images as $imageInfo){
				
				$option_images[$imageInfo['option_id']] = $imageInfo;
				
			}
			
			$data['option_images'] = $option_images;
			
			if($data && is_array($data) && !empty($data)){
				
				$attribute->addData($data);
				
			}						
		}
		
		public function moveImageFromTmp($file)
	    {

	        $ioObject = new Varien_Io_File();
	        $destDirectory = Mage::getBaseDir('media').'/option_image';
	        
	        try {
	            $ioObject->open(array('path'=>$destDirectory));
	        } catch (Exception $e) {
	            $ioObject->mkdir($destDirectory, 0777, true);
	            $ioObject->open(array('path'=>$destDirectory));
	        }

	        if (strrpos($file, '.tmp') == strlen($file)-4) {
	            $file = substr($file, 0, strlen($file)-4);
	        }

	        $destFile = Varien_File_Uploader::getNewFileName($file);
	        
	        $dest = $destDirectory .'/'. $destFile;

	        $ioObject->mv(
	            $this->_getMadiaConfig()->getTmpMediaPath($file),
	            $dest
	        );

	        return $destFile;
	    }
		
		public function saveAttribute($event){
			    					
			$attribute_id		= (int)$event->getAttribute()->getAttributeId();
			$filter_type		= (int)$event->getAttribute()->getData('filter_type');
			$image_align		= (int)$event->getAttribute()->getData('image_align');
			$image_width		= (int)$event->getAttribute()->getData('image_width');
			$image_height		= (int)$event->getAttribute()->getData('image_height');
			$show_minimized		= (int)$event->getAttribute()->getData('show_minimized');
			$show_image_name	= (int)$event->getAttribute()->getData('show_image_name');
			$visible_options	= (int)$event->getAttribute()->getData('visible_options');
			$show_help			= (int)$event->getAttribute()->getData('show_help');
			$show_checkbox		= (int)$event->getAttribute()->getData('show_checkbox');
			$popup_text			= (array)$event->getAttribute()->getData('popup_text');			
			$popup_width		= (int)$event->getAttribute()->getData('popup_width');
			$popup_height		= (int)$event->getAttribute()->getData('popup_height');
			$filter_reset		= (int)$event->getAttribute()->getData('filter_reset');
			$is_ajax		    = (int)$event->getAttribute()->getData('is_ajax');
			$inblock_height		= (int)$event->getAttribute()->getData('inblock_height');
			$filter_button		= (int)$event->getAttribute()->getData('filter_button');
					
			$attribute = Mage::getModel('gomage_navigation/attribute')->load($attribute_id, 'attribute_id');
			
			if (!$attribute->getData('attribute_id')){				
				$attribute->setData('attribute_id', $attribute_id);
				$attribute->isObjectNew(true);
			}	           			 					
	            					
	        $attribute->addData(array('filter_type'	=> $filter_type,
					'image_align' => $image_align,
					'image_width' => $image_width,
					'image_height' => $image_height,
					'show_minimized' => $show_minimized,
					'show_image_name' => $show_image_name,
					'show_checkbox' => $show_checkbox,
					'visible_options' => $visible_options,
					'show_help' => $show_help,
					'popup_width' => $popup_width,
					'popup_height' => $popup_height,
					'filter_reset' => $filter_reset,
					'is_ajax' => $is_ajax,
					'inblock_height' => $inblock_height,
					'filter_button' => $filter_button));
	        												
	        $attribute->save();
	        
			foreach ($popup_text as $store_id => $text){			
				$attribute_store = Mage::getModel('gomage_navigation/attribute_store')	
									->getCollection()							
									->addFieldToFilter('attribute_id', $attribute_id)
	            					->addFieldToFilter('store_id', $store_id)
	            					->getFirstItem();
	            					
	            if (!$attribute_store->getId()){
	            	$attribute_store->setData('attribute_id', $attribute_id);
	            	$attribute_store->setData('store_id', $store_id);
	            }
	            
	            $attribute_store->setData('popup_text', $text);
	            $attribute_store->save();	            					
			}
		}
		
		public function checkAjax(){
			
			if($layout = Mage::getSingleton('core/layout')){
				Mage::log('ajax: '. Mage::app()->getFrontController()->getRequest()->getParam('ajax'), null, 'arush-gomage.log');
				if(intval(Mage::app()->getFrontController()->getRequest()->getParam('ajax'))){
					
					$layout->removeOutputBlock('root');
					$layout->removeOutputBlock('core_profiler');
					
					Mage::log('navblock: '. $layout->getBlock('catalog.leftnav'), null, 'arush-gomage.log');

					if ($layout->getBlock('gomage.catalog.rightnav')){
						$navBlock = $layout->getBlock('gomage.catalog.rightnav');
					}
					elseif($layout->getBlock('catalogsearch.leftnav'))
                    {
						$navBlock = $layout->getBlock('catalogsearch.leftnav');
					}
                    elseif($layout->getBlock('catalog.leftnav'))
                    {
                        $navBlock = $layout->getBlock('catalog.leftnav');
                    }
                    elseif($layout->getBlock('gomage.enterprise.catalogsearch.leftnav'))
                    {
                        $navBlock = $layout->getBlock('gomage.enterprise.catalogsearch.leftnav');
                    }
                    elseif($layout->getBlock('gomage.enterprise.catalog.leftnav'))
                    {
                        $navBlock = $layout->getBlock('gomage.enterprise.catalog.leftnav');
                    }

					if(!($productsBlock = $layout->getBlock('search_result_list'))){
						$productsBlock = $layout->getBlock('product_list');
					}

					$LeftnavBlock = $layout->getBlock('gomage.navigation.left');
					$RightnavBlock = $layout->getBlock('gomage.navigation.right');
					
					$navigation_more_button = $layout->getBlock('gomage.navigation.more.button');

					$navigation_html = '';
					if ($navBlock){
						if (Mage::getStoreConfig('gomage_navigation/general/show_shopby') == GoMage_Navigation_Model_Adminhtml_System_Config_Source_Shopby::CONTENT){
							$navBlock->setShopByInContent(true);
						}
						$navigation_html = Mage::getModel('core/url')->sessionUrlVar($navBlock->toHtml());
						$navBlock->setShopByInContent(false);
					}
					
					$gomage_ajax = Mage::getBlockSingleton('gomage_navigation/ajax');
					$gomage_ajax->addData(
							array(
								'navigation'	    => $navigation_html,
								'product_list'	    => ($productsBlock ? Mage::getModel('core/url')->sessionUrlVar($productsBlock->toHtml()) : ''),
							    'navigation_left'	=> ($LeftnavBlock ? Mage::getModel('core/url')->sessionUrlVar($LeftnavBlock->toHtml()) : ''), 
							    'navigation_right'	=> ($RightnavBlock ? Mage::getModel('core/url')->sessionUrlVar($RightnavBlock->toHtml()) : ''),
								'navigation_more'   => ($navigation_more_button ? Mage::getModel('core/url')->sessionUrlVar($navigation_more_button->toHtml()) : ''),																													
							)							
						);
						
					if (Mage::getStoreConfig('gomage_procart/qty_settings/category_page') && $productsBlock) {						
				        $gomage_ajax->addEvalJs("if (typeof(GomageProcartConfig) != 'undefined') {
				        	gomage_procart_product_list = " . $productsBlock->getProcartProductList() . ";
				        	GomageProcartConfig.initialize(gomage_procart_config);
					        };", "eval_js_procart");	
					}
					
					$gomage_ajax->setNameInLayout('gomage_ajax');
					$layout->setBlock('gomage_ajax', $gomage_ajax);					
					$layout->addOutputBlock($gomage_ajax->getNameInLayout(), 'toJson');
					
				}
				
			}
			
		}
		
	protected function _getMadiaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }

	static public function checkK($event){
			
			$key = Mage::getStoreConfig('gomage_activation/advancednavigation/key');
			
			Mage::helper('gomage_navigation')->a($key);
			
	}
	
	public function setContinueShoppingUrl($event){
	    $session = Mage::getSingleton('checkout/session');	    
	    $url = $session->getContinueShoppingUrl();
	    $url = str_replace('ajax=1', '', $url);
	    $session->setContinueShoppingUrl($url);
	}

	public function prepareFilterParams(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $helper = Mage::helper('gomage_navigation');
        
        if ($helper->isFrendlyUrl()){
        
	        $request = $action->getRequest();
	        $attr = array();
	
	        $attributes = Mage::getSingleton('catalog/layer')->getFilterableAttributes();
	        
	        foreach ($attributes as $attribute){
	            $attr[$attribute->getAttributeCode()]['type'] = $attribute->getBackendType();
	            $options = $attribute->getSource()->getAllOptions();
	            foreach ($options as $option){
	                $attr[$attribute->getAttributeCode()]['options'][$helper->formatUrlValue($option['label'])] = $option['value'];
	            }
	        }                
	        Mage::register('gan_filter_attributes', $attr);
	        
	     	if (($layerParams = $request->getQuery()) && !empty($layerParams)){
	            foreach ($layerParams as $param => $value){
	            	if ($param == 'cat'){
	            		$values = explode(',', $value);
	            		$prepare_values = array();
	            		foreach($values as $_value){
	            			$category = Mage::getModel('catalog/category')->loadByAttribute('url_key', $_value);	            			
	            			if ($category && $category->getId()){
	            				$prepare_values[] = $category->getId();
	            			}
	            		}
	            		$request->setQuery($param, implode(',', $prepare_values));
	            		
	            	}elseif (isset($attr[$param]) && !in_array($attr[$param]['type'], array('price', 'decimal'))){
	            		$values = explode(',', $value);
	            		$prepare_values = array();
	            		foreach($values as $_value){            			
	            			if (isset($attr[$param]['options'][$_value])){
	            				$prepare_values[] = $attr[$param]['options'][$_value];
	            			}
	            		}
	            		
	                    $request->setQuery($param, implode(',', $prepare_values));
	                }                
	            }
	        } 
        }
    }
    		
}