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
	
	class GoMage_Feed_Model_Item_Block_Product extends GoMage_Feed_Model_Item_Block{
		
		public function writeTempFile($curr_page = 0, $length= 50, $filename = ''){
			
			
			$products = $this->getFeed()->getProductsCollection('', $curr_page, $length);
			
			$stock_collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
			
			$content = file_get_contents($this->getFeed()->getDirPath().'/feed-'.$this->getFeed()->getId().'-xml-product-block-template.tmp');
			
			$fp = fopen($this->getFeed()->getTempFilePath(), 'a');
			
			$log_fp = fopen(sprintf('%s/productsfeed/%s', Mage::getBaseDir('media'), 'log-'.$this->getFeed()->getId().'.txt'), 'a');
			
			$log_content = date("F j, Y, g:i:s a").', page:'.$curr_page.', items selected:'.count($products)."\n";
			
			fwrite($log_fp, $log_content);
			fclose($log_fp);
			
			$store = Mage::getModel('core/store')->load($this->getFeed()->getStoreId());
			$root_category = Mage::getModel('catalog/category')->load($store->getRootCategoryId());  
			
			if (Mage::getStoreConfig('gomage_feedpro/imagesettings/enable')){
    			$image_width = intval(Mage::getStoreConfig('gomage_feedpro/imagesettings/width'));
    			$image_height = intval(Mage::getStoreConfig('gomage_feedpro/imagesettings/height'));
    		}else{
    			$image_width = 0;
    			$image_height = 0;
    		}
			
			foreach($products as $_product){
				
				$category_path = array();
				
				$category = null;
				
				foreach($_product->getCategoryIds() as $id){
					
					$_category = $this->getFeed()->getCategoriesCollection()->getItemById($id);
					
					if(is_null($category) || ($category && $_category && $category->getLevel() < $_category->getLevel())){
						
						$category = $_category;
						
					}
					
				}
				
				if($category){
				
					$category_path = array($category->getName());
					
					$parent_id = $category->getParentId();
					
					if($category->getLevel() > $root_category->getLevel()){
						
						while($_category = $this->getFeed()->getCategoriesCollection()->getItemById($parent_id)){
							
							if($_category->getLevel() <= $root_category->getLevel()){
								break;
							}
							
							$category_path[] = $_category->getName();
							$parent_id = $_category->getParentId();
						}						
					}				
				}
				
				$product = new Varien_Object();
				
				if($category){				
					$product->setCategory($category->getName());
					$product->setCategoryId($category->getEntityId());									
					$product->setCategorySubcategory(implode(' -&gt; ', array_reverse($category_path)));
				}else{					
					$product->setCategory('');										
					$product->setCategorySubcategory('');
				}
				
				$template_attributes = $this->getAllVars($content);
								
				$custom_attributes = Mage::getResourceModel('gomage_feed/custom_attribute_collection');
    			$custom_attributes->load();
				
				foreach($template_attributes as $attribute_code){
					
					$value = null;
					
					switch($attribute_code):
					
					case ('parent_sku'):
						if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){
						 	$value = $parent_product->getSku();
						 }else{
						 	$value = '';
						 }	
    				break;
    				
    				case 'parent_base_image':
    					if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){    									
							$_prod = Mage::getModel('catalog/product')->load($parent_product->getId());
    					}else{
    						$_prod = Mage::getModel('catalog/product')->load($_product->getId());
    					}	
    					try{	
                           	if ($image_width || $image_height){
                          		$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image')->resize($image_width, $image_height);
                           	}else{	    						        		    						    		    						     
    							$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image');
                          	}    							
    					}catch(Exception $e){    										
    						$image_url = '';    										
    					}
    					$value = $image_url;																														    								
    				break;
					
					case ('price'):
    								
    					if(in_array($_product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
    					    $value =  $store->convertPrice($_product->getMinimalPrice(), false, false);    						           
    					else 
    						$value = $store->convertPrice($_product->getPrice(), false, false);
    							
    				break;
					
					case ('store_price'):
                    case ('final_price'):
						
						$value = $store->convertPrice($_product->getFinalPrice(), false, false);

					break;

                    case('image'):
					case('gallery'):
					case('media_gallery'):

                           $_prod = Mage::getModel('catalog/product')->load($_product->getId());
                           try
                           {
                           	   if ($image_width || $image_height){
                               		$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image')->resize($image_width, $image_height);
                               }else{	    						        		    						    		    						     
    								$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image');
                               }

						   }catch(Exception $e)
                           {
							   $image_url = '';
						   }
                           $product->setData($attribute_code, $image_url);

					break;
					
					case('image_2'):    							   
					case('image_3'):
					case('image_4'):        
					case('image_5'):    							                                                                                                                                  
                           if (!$_product->hasData('media_gallery_images'))
                           {
                               $_prod = Mage::getModel('catalog/product')->load($_product->getId());
                               $_product->setData('media_gallery_images', $_prod->getMediaGalleryImages());    
                           }
                           $i = 0;                                       
                           foreach ($_product->getMediaGalleryImages() as $_image)
                           {
                               $i++;
                               if (('image_' . $i) == $attribute_code){
                               		if ($image_width || $image_height){                                           			
                                         $product->setData($attribute_code, (string)Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())->resize($image_width, $image_height)); 
                                    }else{
                                         $product->setData($attribute_code, $_image['url']);
                                    }	                                                                   
                               }
                           } 																		
					break;

					
					case ('parent_url'):
						
						if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){
							
							$product->setParentUrl($parent_product->getProductUrl(false));
							
							
							break;
						}
						
						$product->setParentUrl($_product->getProductUrl(false));

					break;

					case('url'):
						$product->setUrl($_product->getProductUrl(false));
					break;

					default:
					
					if(strpos($attribute_code, 'custom:') === 0){
						
						$custom_code = trim(str_replace('custom:', '', $attribute_code));
						
						if($custom_attribute = $custom_attributes->getItemByColumnValue('code', $custom_code)){
							
							$options = Zend_Json::decode($custom_attribute->getData('data'));
														
							foreach($options as $option){
								
								foreach($option['condition'] as $condition){
									
									switch($condition['attribute_code']):    							    							
    							
										case ('product_type'):
    										$attr_value = $_product->getTypeId();
    									break;
	    												
										case ('price'):
    								
					    					if(in_array($_product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
					    					    $attr_value =  $store->convertPrice($_product->getMinimalPrice(), false, false);    						           
					    					else 
					    						$attr_value = $store->convertPrice($_product->getPrice(), false, false);
					    							
					    				break;
					    				
            							case ('store_price'):
            								
            								$attr_value = $store->convertPrice($_product->getFinalPrice(), false, false);
            								
            							break;
            							
            							
            							case ('parent_url'):
            								
            								if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){
            									
        										$attr_value = $parent_product->getProductUrl(false);
        										
        										break;
        										
            								}
            								
            								$attr_value = $_product->getProductUrl(false);
            								
            							break;
            							
            							case('image'):
            							case('gallery'):
            							case('media_gallery'):
        
            							   if (!$_product->hasData('product_base_image'))
                                           {                                                                                                                                                
                                               $_prod = Mage::getModel('catalog/product')->load($_product->getId());
        
                                               try{		    						        		    						    		    						                 										
	                                               if ($image_width || $image_height){
		                                       			$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image')->resize($image_width, $image_height);
		                                       	   }else{	    						        		    						    		    						     
		    											$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image');
		                                       	   }
            										
            								   }catch(Exception $e){    										
            										$image_url = '';    										
            								   }                                                                              
                                               $_product->setData('product_base_image', $image_url);
                                               $attr_value = $image_url;
                                                  
                                           }
                                           else 
                                           {                                                                                           
                                               $attr_value = $_product->getData('product_base_image');
                                           }        							    		    						
        																		
            							break;
            							case('url'):
            								$attr_value = $_product->getProductUrl(false);
            							break;
            							case('qty'):
            								
            								if($stock_item = $stock_collection->getItemByColumnValue('product_id', $_product->getId())){
            								
            									$attr_value = ceil($stock_collection->getItemByColumnValue('product_id', $_product->getId())->getQty());
            								
            								}else{
            								
            									$attr_value = 0;
            								
            								}
            							break;
                                        case('is_in_stock'):
								            if($stock_item = $stock_collection->getItemByColumnValue('product_id', $_product->getId())){

            									$attr_value = $stock_collection->getItemByColumnValue('product_id', $_product->getId())->getData('is_in_stock');

            								}else{

            									$attr_value = 0;

            								}
            							break;
            							case('category'):            								
            								    $attr_value = $product->getCategory();        									        									                        									
            							break;
            							case('category_id'):
            							        $attr_value = $_product->getCategoryIds();            							                                    							        
            							break;    
            							default:	    									    
								                $attr_value = $_product->getData($condition['attribute_code']);
								    endswitch;
								    
									$cond_value = $condition['value'];
									
									$is_multi = false;
									
									if($product_attribute = $_product->getResource()->getAttribute($condition['attribute_code'])){
										
										if($product_attribute->getFrontendInput() == 'multiselect'){
											
											$is_multi = true;
											$attr_value = explode(',', $attr_value);
										
										}
										
									}
									
								    if ($condition['attribute_code'] == 'category_id')
									{
									    $is_multi = true;
									}
									
									switch($condition['condition']):
										
										case('eq'):
											
											if(!$is_multi && $attr_value == $cond_value || $is_multi && in_array($cond_value, $attr_value)){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('neq'):
										
											if(!$is_multi && $attr_value != $cond_value || $is_multi && !in_array($cond_value, $attr_value)){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('gt'):
										
											if($attr_value > $cond_value){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('lt'):
										
											if($attr_value < $cond_value){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('gteq'):
										
											if($attr_value >= $cond_value){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('lteq'):
										
											if($attr_value <= $cond_value){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('like'):
										
											if(strpos($attr_value, $cond_value) !== false){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
										case('nlike'):
										
											if(strpos($attr_value, $cond_value) === false){
												
												continue 2;
												
											}else{
												
												continue 3;
												
											}
										
										break;
										
									endswitch;
									
								
								}

								if (in_array($option['value_type'], array('percent', 'attribute'))){
									
									switch($option['value_type_attribute']):    							    							
    							
										case ('price'):
	    								
					    					if(in_array($_product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
					    					    $attribute_value =  $store->convertPrice($_product->getMinimalPrice(), false, false);    						           
					    					else 
					    						$attribute_value = $store->convertPrice($_product->getPrice(), false, false);
					    							
					    				break;
					    				
	        							case ('store_price'):
	        								
	        								$attribute_value = $store->convertPrice($_product->getFinalPrice(), false, false);
	        								
	        							break;
	        							
	        							
	        							case ('parent_url'):
	        								
	        								if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){
	        									
	    										$attribute_value = $parent_product->getProductUrl(false);
	    										
	    										break;
	    										
	        								}
	        								
	        								$attribute_value = $_product->getProductUrl(false);
	        								
	        							break;
	        							
	        							case('image'):
	        							case('gallery'):
	        							case('media_gallery'):
	    
	        							   if (!$_product->hasData('product_base_image'))
	                                       {                                                                                                                                                
	                                           $_prod = Mage::getModel('catalog/product')->load($_product->getId());
	    
	                                           try{		    						        		    						    		    						             										
		                                            if ($image_width || $image_height){
		                                       			$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image')->resize($image_width, $image_height);
		                                       		}else{	    						        		    						    		    						     
		    											$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image');
		                                       		}
	        										
	        								   }catch(Exception $e){    										
	        										$image_url = '';    										
	        								   }                                                                              
	                                           $_product->setData('product_base_image', $image_url);
	                                           $attribute_value = $image_url;
	                                              
	                                       }
	                                       else 
	                                       {                                                                                           
	                                           $attribute_value = $_product->getData('product_base_image');
	                                       }        							    		    						
	    																		
	        							break;
	        							case('url'):
	        								$attribute_value = $_product->getProductUrl(false);
	        							break;
	        							case('qty'):
	        								
	        								if($stock_item = $stock_collection->getItemByColumnValue('product_id', $_product->getId())){
	        								
	        									$attribute_value = ceil($stock_collection->getItemByColumnValue('product_id', $_product->getId())->getQty());
	        								
	        								}else{
	        								
	        									$attribute_value = 0;
	        								
	        								}
	        							break;
	        							case('category'):
	    									$attribute_value = $product->getCategory();     									
	        							break;
	        							default:	    									    
								            $attribute_value = $_product->getData($option['value_type_attribute']);
								  endswitch;
								
								}
								
								if($option['value_type'] == 'percent'){
									
									$value = floatval($attribute_value)/100*floatval($option['value']);

								}elseif($option['value_type'] == 'attribute'){
	    											
	    							$value = $attribute_value;
	    												
	    						}else{
									
									$value = $option['value'];
									
								}
								
								break;
								
							}
							
							if($value === null && $custom_attribute->getDefaultValue()){
								
								switch($custom_attribute->getDefaultValue()):    							    							
    							
									case ('price'):
    								
				    					if(in_array($_product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)))
				    					    $value =  $store->convertPrice($_product->getMinimalPrice(), false, false);    						           
				    					else 
				    						$value = $store->convertPrice($_product->getPrice(), false, false);
				    							
				    				break;
				    				
        							case ('store_price'):
        								
        								$value = $store->convertPrice($_product->getFinalPrice(), false, false);
        								
        							break;
        							
        							
        							case ('parent_url'):
        								
        								if(($parent_product = $this->getFeed()->getParentProduct($_product, $products)) && $parent_product->getEntityId() > 0){
        									
    										$value = $parent_product->getProductUrl(false);
    										
    										break;
    										
        								}
        								
        								$value = $_product->getProductUrl(false);
        								
        							break;
        							
        							case('image'):
        							case('gallery'):
        							case('media_gallery'):
    
        							   if (!$_product->hasData('product_base_image'))
                                       {                                                                                                                                                
                                           $_prod = Mage::getModel('catalog/product')->load($_product->getId());
    
                                           try{		    						        		    						    		    						             										
	                                            if ($image_width || $image_height){
	                                       			$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image')->resize($image_width, $image_height);
	                                       		}else{	    						        		    						    		    						     
	    											$image_url = (string)Mage::helper('catalog/image')->init($_prod, 'image');
	                                       		}
        										
        								   }catch(Exception $e){    										
        										$image_url = '';    										
        								   }                                                                              
                                           $_product->setData('product_base_image', $image_url);
                                           $value = $image_url;
                                              
                                       }
                                       else 
                                       {                                                                                           
                                           $value = $_product->getData('product_base_image');
                                       }        							    		    						
    																		
        							break;
        							case('url'):
        								$value = $_product->getProductUrl(false);
        							break;
        							case('qty'):
        								
        								if($stock_item = $stock_collection->getItemByColumnValue('product_id', $_product->getId())){
        								
        									$value = ceil($stock_collection->getItemByColumnValue('product_id', $_product->getId())->getQty());
        								
        								}else{
        								
        									$value = 0;
        								
        								}
        							break;
        							case('category'):
    									  	$value = $product->getCategory();     									
        							break;
        							default:	    									    
							            $value = $_product->getData($custom_attribute->getDefaultValue());
							  endswitch;
								
							}
							
						}
						
						
						
					}elseif($attribute_model = $_product->getResource()->getAttribute($attribute_code)){

						switch($attribute_model->getFrontendInput()):
							
							case ('select'): case ('multiselect'):
								
								$value = implode(', ', (array)$_product->getAttributeText($attribute_code));
								
							break;
							
							default:
								
								$value = $_product->getData($attribute_code);
								
							break;
							
						endswitch;
					
					}

					break;

					endswitch;

                    if($value && !$product->getData($attribute_code)){
						$product->setData($attribute_code, $value);
					}
						
				}
				
				$product->setDescription(strip_tags(preg_replace('/<br.*?>/s', "\r\n", $_product->getDescription())));
				$product->setShortDescription(strip_tags(preg_replace('/<br.*?>/s', "\r\n", $_product->getShortDescription())));
								
				$product->setQty(ceil(Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty()));
				
				fwrite($fp, parent::setVars($content, $product)."\r\n");
				
			}
			
			
			fclose($fp);
			
		}
		
		public function setVars($content = null, $dataObject=null, $remove_empty=false){
			
			$template_temp_file = $this->getFeed()->getDirPath().'/feed-'.$this->getFeed()->getId().'-xml-product-block-template.tmp';
			
			file_put_contents($template_temp_file, $content);
			
			$collection = $this->getFeed()->getProductsCollection();
			$total_products = $collection->getSize();
			
			$per_page = intval($this->getFeed()->getIterationLimit());
			
			if($per_page){
			
				$pages = ceil($total_products/$per_page);
			
			}else{
				
				$pages = 1;
				$per_page = 0;
			}
		
			file_put_contents(sprintf('%s/productsfeed', Mage::getBaseDir('media')).'/log-'.$this->getFeed()->getId().'.txt', "started at:".date("F j, Y, g:i:s a").", pages:{$pages}, per_page:{$per_page} \n");

			for($i = 0;$i<$pages;$i++){

				if ($_fp = fopen(Mage::getSingleton('core/store')->load($this->getFeed()->getStoreId())->getUrl('feed/index/index', array('id'=>$this->getFeed()->getId(), 'start'=>$i, 'length'=>$per_page, '_nosid' => true)), 'r')){

					$contents = '';
					while (!feof($_fp)) {
					  $contents .= fread($_fp, 8192);
					}

		            $response = Zend_Json::decode($contents);
		            
		            if(!isset($response['result']) || !$response['result']){
		            	
		            	throw new Mage_Core_Exception(Mage::helper('gomage_feed')->__('There was an error while generating file. Change "Number of Products" in the Advanced Settings.
                                                                Try to change "Number of Products" in the Advanced Settings.
                                                                For example: set "Number of Products" equal 50 or 100.'));
		            	
		            }
		            
		        }else{
		        	
		        	throw new Mage_Core_Exception(Mage::helper('gomage_feed')->__('Cant open temp file'));
		        	
		        }
		        
		        
		        fclose($_fp);

	        }
	        
			$content = file_get_contents($this->getFeed()->getTempFilePath());
			
			unlink($template_temp_file);
			unlink($this->getFeed()->getTempFilePath());

			return $content;
			
		}
		
	}