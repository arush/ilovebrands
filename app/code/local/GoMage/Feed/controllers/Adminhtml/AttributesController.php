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

	class GoMage_Feed_Adminhtml_AttributesController extends Mage_Adminhtml_Controller_Action{
	/*
	protected function _redirect($path, $arguments=array()){
    	
        $this->getResponse()->setRedirect(Mage::getModel('core/store')->load('0')->getUrl($path, $arguments));
        
        return $this;
    }
    /**/
	
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('catalog/gomage_feed')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Feed Manager'), Mage::helper('adminhtml')->__('Feed Manager'));
		
		return $this;
	}   
 	
	public function indexAction() {
		$this->_initAction();
		$this->renderLayout();
		
	}
	
	public function newAction(){
		$this->_initAction();
		
		if($data = Mage::getSingleton('core/session')->getCustomAttributeData()){
        	Mage::register('gomage_custom_attribute', Mage::getModel('gomage_feed/custom_attribute')->addData($data));
        	Mage::getSingleton('core/session')->setCustomAttributeData(null);
        }
		
		$this->_addContent($this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit'))
				->_addLeft($this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit_tabs'));
	
		$this->renderLayout();
		
	}
	
	public function editAction(){
		$this->_initAction();
		
		if($id = $this->getRequest()->getParam('id')){
        	Mage::register('gomage_custom_attribute', Mage::getModel('gomage_feed/custom_attribute')->load($id));
        }
		
		$this->_addContent($this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit'))
				->_addLeft($this->getLayout()->createBlock('gomage_feed/adminhtml_attributes_edit_tabs'));
	
		$this->renderLayout();
		
	}
	
	public function saveAction(){
		if ($data = $this->getRequest()->getPost()) {
			
			try{
			    $id = $this->getRequest()->getParam('id');
			    
				$model = Mage::getModel('gomage_feed/custom_attribute');
				
				if(isset($data['option'])){
				
				$data['data'] = Zend_Json::encode((array)$data['option']);
				
				}
				
				unset($data['option']);
				
	            $model->setData($data)->setId($id)->save();
	            
	            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('core')->__('Dynamic attribute was successfully saved'));
	            
	            if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				
            }catch(Mage_Core_Exception $e){
            	
            	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            	
            	Mage::getSingleton('core/session')->setCustomAttributeData($data);
            	
            	if($model->getId()>0){
            		$this->_redirect('*/*/edit', array('id' => $model->getId()));
            	}else{
            		$this->_redirect('*/*/new', array('type' => $model->getType()));
            	}
            	return false;
            	
            }catch(Exception $e){
            	
            	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Can’t save data'));
            	
				Mage::getSingleton('core/session')->setCustomAttributeData($data);
				
            	if($model->getId()>0){
            		$this->_redirect('*/*/edit', array('id' => $model->getId()));
            	}else{
            		$this->_redirect('*/*/new', array('type' => $model->getType()));
            	}
            	return false;
            	
            }
            $this->_redirect('*/*/');
        }
	}
	
	public function deleteAction(){
		
		if($id = intval($this->getRequest()->getParam('id'))){
			
			$this->_deleteItems(array($id));
			
		}
		$this->_redirect('*/*/');
	}
	
	public function massDeleteAction(){
		
		if($ids = $this->getRequest()->getParam('id')){
			if(is_array($ids) && !empty($ids)){
				$this->_deleteItems($ids);
			}
			
		}
		
		$this->_redirect('*/*/');
		
	}
	protected function _deleteItems($ids){
		if(is_array($ids) && !empty($ids)){
			
			$model = Mage::getModel('gomage_feed/custom_attribute');
			
			foreach($ids as $id){
				
				$item = $model->setId($id)->delete();
				
			}
		}
	}
	
	public function getattributevaluefieldAction(){
    	
    	$result = array();
    	
    	$name = $this->getRequest()->getParam('element_name');
    	$condition_name = $this->getRequest()->getParam('condition_name');
    		
    	if($code = $this->getRequest()->getParam('attribute_code')){
    		    		
    		$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
    		
    		if( ($attribute && ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')) ||
    			($code == 'product_type') ){
	        	
	        	$options = array();
	        	
	        	if ($code == 'product_type'){
	        		$attribute_options = Mage_Catalog_Model_Product_Type::getOptions();
	        	}else{
	        		$attribute_options = $attribute->getSource()->getAllOptions();
	        	}
			
				foreach($attribute_options as $option){
					
					extract($option);
					
					$options[] = "<option value=\"{$value}\">{$label}</option>";
					
				}
				
				$current = '';
				
				$conditions = array(
				'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.$this->__('equal').'</option>',
				'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.$this->__('not equal').'</option>',
				);
				$result['condition']	= '<select style="width:120px" name="' . $condition_name . '">'.implode('', $conditions).'</select>';
				$result['select'] = '<select style="width: 100%; border: 0pt none; padding: 0pt;" name="'.$name.'">'.implode('', $options).'</select>';					        	
	        }
	        
    	}
    	
    	if(empty($result)){
    		$current = '';
    		$conditions = array(
			'<option '.($current == 'eq' ? 'selected="selected"' : '').' value="eq">'.$this->__('equal').'</option>',
			'<option '.($current == 'neq' ? 'selected="selected"' : '').' value="neq">'.$this->__('not equal').'</option>',
			'<option '.($current == 'gt' ? 'selected="selected"' : '').' value="gt">'.$this->__('greater than').'</option>',
			'<option '.($current == 'lt' ? 'selected="selected"' : '').' value="lt">'.$this->__('less than').'</option>',
			'<option '.($current == 'gteq' ? 'selected="selected"' : '').' value="gteq">'.$this->__('greater than or equal to').'</option>',
			'<option '.($current == 'lteq' ? 'selected="selected"' : '').' value="lteq">'.$this->__('less than or equal to').'</option>',
			'<option '.($current == 'like' ? 'selected="selected"' : '').' value="like">'.$this->__('like').'</option>',
			'<option '.($current == 'nlike' ? 'selected="selected"' : '').' value="nlike">'.$this->__('not like').'</option>',
			);
		
			$result['condition']	= '<select style="width:120px" name="' . $condition_name . '">'.implode('', $conditions).'</select>';
        	$result['select']		= ('<input style="width:100%;border:0;padding:0;" type="text" class="input-text" name="'.$name.'" value=""/>');
        	
        }
    	
    	$this->getResponse()->setBody(Zend_Json::encode($result));
    	
    }
    
	public function mappingexportAction(){
		
		
		if($id = $this->getRequest()->getParam('id')){
			
						
			$model = Mage::getModel('gomage_feed/custom_attribute')->load($id);
			
			$this->getResponse()->setBody($model->getData('data'));
			$this->getResponse()->setHeader('Content-Type','text');
			$this->getResponse()->setHeader('Content-Disposition','attachment; filename="mapping-export-'.basename($model->getCode()).'.txt";');
			
		}
		
	}
	
	public function mappingimportAction() {
		
		if(($post = $this->getRequest()->getPost()) && ($id = $this->getRequest()->getParam('id')) && isset($_FILES['mappingfile']) && $_FILES['mappingfile']){
			
			try{
				
				$data = file_get_contents($_FILES['mappingfile']['tmp_name']);
				
				$array_data = json_decode($data);
				
				if(empty($array_data)){
					
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Empty or Invalid data file'));
					
				}else{
					
					Mage::getModel('gomage_feed/custom_attribute')->load($id)->setData('data', $data)->save();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('core')->__('Data successfully imported'));
					
				}
				
			}catch(Exception $e){
				
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Unknown error'));
				
			}
			
			return $this->_redirect('*/*/edit', array('id'=>$id, 'tab'=>'data_section'));
		}
		
		$this->_initAction();
		if($id = $this->getRequest()->getParam('id')){
        	Mage::register('gomage_custom_attribute', Mage::getModel('gomage_feed/custom_attribute')->load($id));
			$this->_addContent($this->getLayout()->createBlock('gomage_feed/adminhtml_items_mappingimport'))
				->_addLeft($this->getLayout()->createBlock('gomage_feed/adminhtml_items_mappingimport_tabs'));

		}
		$this->renderLayout();
	}

}