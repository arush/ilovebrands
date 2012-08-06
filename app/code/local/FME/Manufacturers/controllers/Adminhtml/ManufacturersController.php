<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME 
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Manufacturers_Adminhtml_ManufacturersController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('manufacturers/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manufacturers Manager'), Mage::helper('adminhtml')->__('Manufacturer Manager'));
		$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	protected function _initManufacturerProducts() {
		
		$manufacturers = Mage::getModel('manufacturers/manufacturers');
        $manufacturerId  = (int) $this->getRequest()->getParam('id');
		if ($manufacturerId) {
        	$manufacturers->load($manufacturerId);
		}
		Mage::register('current_manufacturer_products', $manufacturers);
		return $manufacturers;
		
	}


	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('manufacturers/manufacturers')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('manufacturers_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('manufacturers/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit'))
				->_addLeft($this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturers')->__('Manufacturer does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		
		if ($data = $this->getRequest()->getPost()) {
			
			//Upload Logo 
			$files = $this->uploadFiles( $_FILES ); 
            if( $files && is_array($files) ){
                for( $f=0; $f<count($files); $f++ ){ 
                    if( $files[$f] ){
                        $fieldname = str_replace('_uploader','',$files[$f]['fieldname']);
                        if( array_key_exists($fieldname, $data) ){
							$data['m_logo'] = str_replace('\\', '/', $files[$f]['url']);
                        }   
                    }  
                }  
            }
				
			//Set Related Products
			 $links = $this->getRequest()->getPost('links');
			 if (isset($links['related'])) {
				$productIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']);
				 $productString = "";
				 foreach ($productIds as $_product) {
					$productString .= $_product.",";
				 }
				 $_POST['productIds'] = $productString;
			 }
				
			$model = Mage::getModel('manufacturers/manufacturers');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				
				if(!$this->getRequest()->getParam('id')){
					//Used to insert FME Manufacturers to Magento Attributes
					$this->insertEavAttributes($this->getRequest()->getPost('m_name'),$model->getId());
				}else{
					//Used to update FME Manufacturers to Magento Attributes
					$this->updateEavAttributes($this->getRequest()->getPost('m_name'), $model->getId());
				}
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__('Manufacturer was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturers')->__('Unable to find Manufacturer to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				
			    $id     = $this->getRequest()->getParam('id');
				$model = Mage::getModel('manufacturers/manufacturers');
				$object  = Mage::getModel('manufacturers/manufacturers')->load($id);
				$model->setId($this->getRequest()->getParam('id'));
		
				//Delete All Associated Products Links
				Mage::getModel('manufacturers/manufacturers')->deleteManufacturerStores($id);
				
				//Delete All Associated Store Links
				Mage::getModel('manufacturers/manufacturers')->deleteManufacturerProductLinks($id);

				//Delete All Magento Attributes
				Mage::getModel('manufacturers/manufacturers')->deleteBrandsAttributes($id);
				
				//Delete Main Table data
				$model->delete();
				 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Manufacturer was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	
	/*
	*	Function used to insert into magento attributes values
	*	$brand_name: this is used for to insert option value	
	*/
	public function insertEavAttributes($brand_name, $brand_id){
		$attributeCode = Mage::helper('manufacturers')->getAttributeCode();
		
		$eav_attribute_option_value_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
		$eav_attribute_option_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option');
		$eav_attribute_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$sqry = "SELECT attribute_id FROM ".$eav_attribute_table." WHERE attribute_code = '".$attributeCode."'";
		$select = $read->query($sqry);
		$attributes = $select->fetchAll();
		
		$sql1  = "insert into ".$eav_attribute_option_table." (attribute_id ,sort_order) values ('".$attributes[0]['attribute_id']."', 0)";
		$write->query($sql1);
		
		$pqry = "SELECT option_id FROM ".$eav_attribute_option_table." order by option_id desc limit 1";
		$selectp = $read->query($pqry);
		$options = $selectp->fetchAll();
		
		// Inserting with store Id 0
		$sql2  = "insert into ".$eav_attribute_option_value_table." (option_id ,store_id, `value`) values ('".$options[0]['option_id']."', 0, '".$brand_name."')";
		$write->query($sql2);
		// Get all other stores and insert accordingly
		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val){
			$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
			$sql2  = "insert into ".$eav_attribute_option_value_table." (option_id ,store_id, `value`) values ('".$options[0]['option_id']."', ".$_storeId.", '".$brand_name."')";
			$write->query($sql2);
		}
		
		//updating manufacturer table with option_id
		$sql4  = "update ".$manufacturer_Table." set option_id = ".$options[0]['option_id']." where manufacturers_id = ".$brand_id;
		$write->query($sql4);
		
		//insert into Catalog product entity int table
		$this->updateCatalogProductEntityInt($brand_id);
	}
	/*
	*	Function used to update magento attributes values with FME Manufacturer
	*	$brand_name: this is used for to update option value	
	*/
	public function updateEavAttributes($brand_name, $brand_id){		
		$eav_attribute_option_value_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql2  = "Update ".$eav_attribute_option_value_table." set `value` = '".$brand_name."' where option_id = ".Mage::helper('manufacturers')->geBrandsOptionId($brand_id);
		$write->query($sql2);
		
		//insert into Catalog product entity int table
		$this->updateCatalogProductEntityInt($brand_id);
	}
	
	
	public function updateCatalogProductEntityInt($brand_id){
		//updating the catalog_product_entity_int table
		$attributeCode = Mage::helper('manufacturers')->getAttributeCode();
		$eav_attribute_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute');	
		$catalog_product_entity_int = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');	
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sqry = "SELECT attribute_id FROM ".$eav_attribute_table." WHERE attribute_code = '".$attributeCode."'";
		$select = $read->query($sqry);
		$attributes = $select->fetchAll();
		
		if(isset($_POST['productIds'])) {
			if($productIdsString != '') {
				$sql1  = "DELETE FROM ".$catalog_product_entity_int." WHERE entity_id in ($productIdsString) and attribute_id = ".$attributes[0]['attribute_id'];
				$write->query($sql1);
			}
		}
		
		$Result = array_unique($productIds);
				
		 foreach ($Result as $_productId) {
			$sql2  = "insert into ".$catalog_product_entity_int." (entity_type_id ,attribute_id, store_id, entity_id, `value`) values (4, ".$attributes[0]['attribute_id'].", 0, ".$_productId.", ".Mage::helper('manufacturers')->getBrandsOptionId($brand_id).")";
			$write->query($sql2);
		 }	
	}

	/**
     * Get related products grid and serializer block
     */
    public function productsAction()
    {
		$this->_initManufacturerProducts();
		$this->loadLayout();
        $this->getLayout()->getBlock('manufacturers.edit.tab.products')
		 				  ->setManufacturerProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }
	
	/**
     * Get related products grid
     */
    public function productsGridAction()
    {
        $this->_initManufacturerProducts();
		//Push Existing Values in Array
		$productsarray = array();
		$manufacturerId  = (int) $this->getRequest()->getParam('id');
		foreach (Mage::registry('current_manufacturer_products')->getManufacturerRelatedProducts($manufacturerId) as $products) {
           $productsarray = $products["product_id"];
        }
		array_push($_POST["products_related"],$productsarray);
		Mage::registry('current_manufacturer_products')->setManufacturerProductsRelated($productsarray);
		
		$this->loadLayout();
        $this->getLayout()->getBlock('manufacturers.edit.tab.products')
            			  ->setManufacturerProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $manufacturersIds = $this->getRequest()->getParam('manufacturers');
        if(!is_array($manufacturersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Manufacturer(s)'));
        } else {
            try {
                foreach ($manufacturersIds as $manufacturersId) {
                    
					$manufacturers = Mage::getModel('manufacturers/manufacturers')->load($manufacturersId);
					/*$pathImg = BP . DS . 'media' . DS . $manufacturers->getMLogo();
					if ($pathImg) {	
						unlink($pathImg); 
					}
					*/
					//Delete All Associated Products Links
					Mage::getModel('manufacturers/manufacturers')->deleteManufacturerStores($manufacturersId);
					
					//Delete All Associated Store Links
					Mage::getModel('manufacturers/manufacturers')->deleteManufacturerProductLinks($manufacturersId);
					
					//Delete All Magento Attributes
					Mage::getModel('manufacturers/manufacturers')->deleteBrandsAttributes($manufacturersId);
					
                    $manufacturers->delete();
					
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($manufacturersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $manufacturersIds = $this->getRequest()->getParam('manufacturers');
        if(!is_array($manufacturersIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Manufacturer(s)'));
        } else {
            try {
                foreach ($manufacturersIds as $manufacturersId) {
                    $manufacturers = Mage::getSingleton('manufacturers/manufacturers')
                        ->load($manufacturersId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($manufacturersIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    /**
     * Import and export Page
     *
     */
  	public function importExportAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('manufacturers/import')
            ->_addContent($this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_importExport'))
            ->renderLayout();
    }
    
     /**
     * Import Manufacturer
     *
     */
  	public function importManufacturerAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('manufacturers/importm')
            ->_addContent($this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_importManufacturer'))
            ->renderLayout();
    }
	
	 /**
     * Import Manufacturer by Attribute Code
     *
     */
  	public function importbyAttributeAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('manufacturers/importbycode')
            ->_addContent($this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_importbyCode'))
            ->renderLayout();
    }
	
	/**
     * export action from import/export Manufacturers
     *
     */
    public function exportPostAction()
    {
        $fileName   = 'manufacturers.csv';
        $content    = $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_ExportGrid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
	
	/**
     * import action from import/export Manufacturers
     *
     */
    public function importPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_manufacturers_file']['tmp_name'])) {
            try {
                $number = $this->_importManufacturersAdmin();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__('%d new item(s) were imported',$number));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturers')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturers')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/importExport');
    }
    
    /**
     * import action from import Manufacturer
     *
     */
    public function importmPostAction()
    {
		
		$remove = array();
		$remove[] = "'";
		$remove[] = '"';
				
		$attributeCode = Mage::helper('manufacturers')->getAttributeCode();
		
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$manufacturer_storeTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_store');
		$manufacturer_productsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
		$_product = Mage::getModel('catalog/product');
			$_attributes = Mage::getResourceModel('eav/entity_attribute_collection')
				->setEntityTypeFilter($_product->getResource()->getTypeId())
			->addFieldToFilter('attribute_code', $attributeCode);
			$_attribute = $_attributes->getFirstItem()->setEntity($_product->getResource());
		$manufacturers = $_attribute->getSource()->getAllOptions(false);
		//echo "<pre>";print_r($manufacturers);exit;
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$ccm = $resource->getTableName('manufacturers');
		$select = $read->select('')
			  ->from( array('ccp'=>$ccm) );
		
		$select_id = 'SELECT manufacturers_id as id FROM '.$manufacturer_Table.' ORDER BY manufacturers_id DESC LIMIT 1';
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid=$last_id[0]['id']+1;
		   
		$alrdy_manuf = $read->fetchAll($select);
		foreach($alrdy_manuf as $alm){
			$allm[]= $alm['m_name'];
		}
		$total_imported=0;
		$total_manuf = count($manufacturers);
		if($total_manuf>0) {
			for($i=0; $i<$total_manuf; $i++) {
				$mid 	= str_replace($remove, "", $manufacturers[$i]['value']);
				$mname	= str_replace($remove, "", $manufacturers[$i]['label']);
				 
				$reg_ex = "/[[:space:]]/";
				$replace_word = "-"; 
				$identifier = preg_replace($reg_ex, $replace_word, $mname); 

				
				$products = Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToSelect('*') // add your custom attribute
				->addAttributeToFilter($attributeCode, $mid); // filter on value 
		
				if(!in_array($mname,$allm)){
					$sql  = "insert into ".$manufacturer_Table." (manufacturers_id,m_name,identifier,m_manufacturer_page_title,m_manufacturer_meta_keywords,m_manufacturer_meta_description,status,option_id) values ('".$mlasid."', '".$mname."' , '".strtolower($identifier)."' , '".$mname."' , '".$mname."' , '".$mname."', '1','".$mid."')";
					
					$write->query($sql);
					
								
					$sql2  = "insert into ".$manufacturer_storeTable." (manufacturers_id,store_id) values (".$mlasid.", 0)";
					$write->query($sql2);
	
					if(count($products->getItems())){
						foreach($products->getItems() as $_p){
							$sql  = "insert into ".$manufacturer_productsTable." (manufacturers_id, product_id) values (".$mlasid.", ".$_p->getId().")";
							$write->query($sql);
						}
					}
					$total_imported++;
					$mlasid++;
				}
			}
		}
			
		if($total_imported > 0){
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__($total_imported.' Manufacturers Imported successfully from manufacturer attribute.'));
		} else {
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__('No Manufacturer imported.'));
		}	
			$this->_redirect('*/*/index');
    }
    
    function getManufacturers() {
        

        return $manufacturers;
    } 
  
    protected function _importManufacturersAdmin()
    {
        $fileName   = $_FILES['import_manufacturers_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		$number = 0;
        /** checks columns */
        $csvFields  = array(
            0    => 'Name',
			1    => 'Identifier',
            2    => 'Website',
            3    => 'Address',
            4    => 'Logo',
			5    => 'Featured',
			6    => 'Contact Name',
			7    => 'Contact Phone',
			8    => 'Contact Fax',
			9    => 'Contact Email',
			10    => 'Contact Address',
			11   => 'Manufacturer Details',
			12   => 'Status',
			13   => 'Products'
        );
		

        if ($csvData[0] == $csvFields) {
            foreach ($csvData as $k => $v) {
                if ($k == 0) {
                    continue;
                }

                //end of file has more then one empty lines
                if (count($v) <= 1 && !strlen($v[0])) {
                    continue;
                }

                if (count($csvFields) != count($v)) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturers')->__('Invalid file upload attempt'));
                }
				
				
				
				if (!empty($v[0])) {
					
				    $v[0] = trim(preg_replace('/[^\w\s-]/','',$v[0]));					
					$v[3] = trim(preg_replace('/[^\w\s-]/','',$v[3]));
					$v[5] = trim(preg_replace('/[^\w\s-]/','',$v[5]));
					$v[6] = trim(preg_replace('/[^\w\s-]/','',$v[6]));
					$v[10] = trim(preg_replace('/[^\w\s-]/','',$v[10]));
					$v[11] = trim(preg_replace('/[^\w\s-]/','',$v[11]));
					$v[12] = trim(preg_replace('/[^\w\s-]/','',$v[12]));
					$v[13] = trim(preg_replace('/[^\w\s-]/','|',$v[13]));						
					
					$resource = Mage::getSingleton('core/resource');
					$read= $resource->getConnection('core_read');				
					$mmnadminTable = $resource->getTableName('manufacturers/manufacturers');
					$select = $read->select()
											->from($mmnadminTable,array('manufacturers_id'))
											->where("m_name=?",$v[0])									
											->limit(1);
									
					if($read->fetchOne($select)){
						 continue;
					}

					
					//Manufacturer Products
				    $productidsString  = trim($v[13], '|');
					$productIds = explode("|", $productidsString);		
					$result = array_unique($productIds);
					$comma_separated = implode(",", $result);
					$_POST['productIds'] = $comma_separated;					
					
					//Set Store ID
					$storesArray = array();
					$storeID = Mage_Core_Model_App::ADMIN_STORE_ID;
					$storesArray[] = $storeID;
					$_POST['stores'] =  $storesArray;
					
					
					if($this->importLogo(false,$v[4]) != "" && $this->importLogo(false,$v[4]) != NULL) {
						//Upload Image
						$data["m_logo"] = $this->importLogo(false,$v[4]);
						
						try {
							$imageFile =  $data['m_logo'];
							$data['m_logo_thumb'] = '<img src="'.Mage::helper('manufacturers')->getResizedUrl($imageFile,75,75,Mage::helper('manufacturers')->getLogoBackgroundColor()).'" border="0" />';
							//End of save Grid image
						} catch(Exception $e) {}
					}
					
					
					$data  = array(
						'm_name'=>$v[0],
						'identifier'=>$v[1],
						'm_website' => $v[2],
						'm_address' => $v[3],
						'm_logo'  => $data["m_logo"],
						'm_logo_thumb'=>$data['m_logo_thumb'],
						'm_featured' => $v[5],
						'm_contact_name' => $v[6],
						'm_contact_phone'  => $v[7],
						'm_contact_fax'=>$v[8],
						'm_contact_email' => $v[9],
						'm_contact_address' => $v[10],
						'm_details'  => $v[11],
						'status'=>$v[12],
						'option_id'=>$v[12]
					);
														
					$model = Mage::getModel('manufacturers/manufacturers');		
					$model->setData($data)
						  ->setId($this->getRequest()->getParam('id'));
			
					try {
						if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
							$model->setCreatedTime(now())
								->setUpdateTime(now());
						} else {
							$model->setUpdateTime(now());
						}	
						$model->save();
						//Used to insert FME Manufacturers to Magento Attributes
						$this->insertEavAttributes($v[0],$model->getId());
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						Mage::getSingleton('adminhtml/session')->setFormData($data);
						$this->_redirect('*/*/importExport');
						return;
					}
					$number++;
                }
            }  
        }
        else {
            Mage::throwException(Mage::helper('manufacturers')->__('Invalid file format upload attempt'));
        }	
		return $number;
    }
  
  
    public function exportCsvAction()
    {
        $fileName   = 'manufacturers.csv';
        $content    = $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'manufacturers.xml';
        $content    = $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	
	protected function uploadFiles( $files ){
        if( !empty($files) && is_array($files) ){
            $result = array();
            foreach( $files as $file=>$info ){
                $result[] = $this->uploadFile( $file );
            }
            return $result;
        }
    }
	
	protected function importLogo($move = false, $logoFile) {
				
		$file = Mage::getBaseDir('media') . DS . $logoFile;
		$baseMediaPath = Mage::getBaseDir('media') . DS .  'manufacturers' . DS . 'files';
		$dynamicScmsURL = 'manufacturers' . DS . 'files';
		
		$pathinfo = pathinfo($file);
		$fileName       = Varien_File_Uploader::getCorrectFileName($pathinfo['basename']);
		$dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
		$fileName       = $dispretionPath . DS . $fileName;
		$destinationFilePath = $baseMediaPath;
		
		$fileName = $dispretionPath . DS . Varien_File_Uploader::getNewFileName($file);
		$destinationDIR = $baseMediaPath . $fileName;

		$ioAdapter = new Varien_Io_File();
		$ioAdapter->setAllowCreateFolders(true);
		$distanationDirectory = dirname($destinationDIR);
		
		try {		
			$ioAdapter->open(array(
				'path'=>$distanationDirectory
			));

			if ($move) {
				$ioAdapter->mv($file, $destinationDIR);
			} else {
				$ioAdapter->cp($file, $destinationDIR);
				$ioAdapter->chmod($destinationDIR, 0777);
			}
		}
		catch (Exception $e) {
			Mage::throwException(Mage::helper('catalog')->__('Failed to move file: %s', $e->getMessage()));
		}
		
		return $dynamicScmsURL.$fileName;
	}
	
	protected function uploadFile( $file_name ){

        if( !empty($_FILES[$file_name]['name']) ){
            $result = array();
            $dynamicScmsURL = 'manufacturers' . DS . 'files';
            $baseScmsMediaURL = Mage::getBaseUrl('media') . DS . 'manufacturers' . DS . 'files';
            $baseScmsMediaPath = Mage::getBaseDir('media') . DS .  'manufacturers' . DS . 'files';
            
            $uploader = new Varien_File_Uploader( $file_name );
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf','xls','xlsx','doc','docx'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save( $baseScmsMediaPath );
       
            $file = str_replace(DS, '/', $result['file']);
            if( substr($baseScmsMediaURL, strlen($baseScmsMediaURL)-1)=='/' && substr($file, 0, 1)=='/' )    $file = substr($file, 1);
						
            $ScmsMediaUrl = $dynamicScmsURL.$file;
            
            $result['fieldname'] = $file_name;
            $result['url'] = $ScmsMediaUrl;
            $result['file'] = $result['file'];
            return $result;
        } else {
            return false;
        }
    } 
		
	/**
     * Get faqs products grid and serializer block
     */
    public function productAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Get faqs products grid
     */
    public function productGridAction()
    {
	echo 'Function ===> productgridaction';
        $this->_initProduct();
        $this->loadLayout();
		$data=$this->getRequest()->getPost();
        $this->renderLayout();
    }
     
    public function gridAction()
	{
		 
	    $this->getResponse()->setBody(
            $this->getLayout()->createBlock('manufacturers/adminhtml_manufacturers_edit_tab_product')->toHtml()
        );
	
	}

    /**
     * Get specified tab grid
     */
    public function gridOnlyAction()
    {
        echo 'Function ===> GridOnlyAction';
		$this->_initProduct();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/manufacturers_edit_tab_product')
                ->toHtml()
        );
    }
	
	public function importbyattributeCodeAction()
    {
				
		// specify the attribute code
		$attributeCode = trim($_POST["import_attributecode"]);
		$totalImported=0;
		
		// Create table alias
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$manufacturer_storeTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_store');
		$manufacturer_productsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
		//Get all unique values of attribute
		$attributeValuesArray = Mage::helper('manufacturers')->getAttributeUniqueValues($attributeCode);
		
		//Database objects
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$ccm = $resource->getTableName('manufacturers');
		$select = $read->select('')
			  ->from( array('ccp'=>$ccm) );
		
		//Get lastid from manufacturer table
		$select_id = 'SELECT manufacturers_id as id FROM '.$manufacturer_Table.' ORDER BY manufacturers_id DESC LIMIT 1';
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid=$last_id[0]['id']+1;
				
		//Get total of attributeValues
		$countattributeValues = count($attributeValuesArray);
		
		/*echo Mage::helper('manufacturers')->getAttributeProducts('Raymarine');
		
		echo "<pre>";
		echo $mlasid;
		echo "<br>";
		echo $countattributeValues;
		print_r($attributeValuesArray);exit;*/
		
		//Remove single quote from manufacturer name
		$remove = array();
		$remove[] = "'";
		$remove[] = '"';
		
		//If manufacturer already exists
		$alreadyExists = $read->fetchAll($select);
		foreach($alreadyExists as $manufacturer){
			$allManufacturerArray[]= $manufacturer['m_name'];
		}
		
		if($countattributeValues > 0) {	
			for($i=0; $i<$countattributeValues; $i++) { 
				
				$attributeValue = str_replace($remove, "", $attributeValuesArray[$i]);
				$reg_ex = "/[[:space:]]/";
				$replace_word = "-"; 
				$identifier = preg_replace($reg_ex, $replace_word, $mname);
				
				$products = Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToSelect('*') // add your custom attribute
				->addAttributeToFilter($attributeCode, $attributeValue); // filter on value 	
				$products->getSelect()->distinct(true);

				
				if(!in_array($attributeValue,$allManufacturerArray)){
					//Insert the values in Manufacturer table
					$sqlManufacturer  = "insert into ".$manufacturer_Table." (manufacturers_id,m_name,identifier,m_manufacturer_page_title,m_manufacturer_meta_keywords,m_manufacturer_meta_description,status) values ('".$mlasid."', '".$attributeValue."' , '".strtolower($identifier)."' , '".$attributeValue."' , '".$attributeValue."' , '".$attributeValue."', '1')";
					$write->query($sqlManufacturer);
					
					//Insert the values in Manufacturer store table
					$sqlManufacturerStore  = "insert into ".$manufacturer_storeTable." (manufacturers_id,store_id) values (".$mlasid.", 0)";
					$write->query($sqlManufacturerStore);
					
					if(count($products->getItems())){
						foreach($products->getItems() as $_p){
							$sqlManufacturerProducts  = "insert into ".$manufacturer_productsTable." (manufacturers_id, product_id) values (".$mlasid.", ".$_p->getId().")";
							$write->query($sqlManufacturerProducts);
						}
					}
					
					$totalImported++;
					$mlasid++;	
				}
			}

		}
			
		if($totalImported > 0){
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__($totalImported.' Manufacturers Imported successfully from manufacturer attribute.'));
		} else {
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturers')->__('No Manufacturer imported.'));
		}	
			$this->_redirect('*/*/index');
    }

	
}
