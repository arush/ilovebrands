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

class GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Content_Csv extends Mage_Adminhtml_Block_Template
{
	
	protected $attribute_collection;
	protected $options;
	protected $_config; 
	
	public function __construct()
    {
        parent::__construct();                
        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/ajaxupload'));        
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(array(            
            'all'    => array(
                'label' => Mage::helper('adminhtml')->__('All Files'),
                'files' => array('*.*')
            )
        ));
    } 
    
    public function getHtmlId()
    {
        if ($this->getData('upload_id')===null) {
            $this->setData('upload_id', 'id_gomage_feed_upload' );
        }
        return $this->getData('upload_id');
    }
    
    public function getConfigJson()
    {
        return Zend_Json::encode($this->getConfig()->getData());        
    }
    
    public function getConfig()
    {
        if(is_null($this->_config)) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }
       
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }
    
    public function getPostMaxSize()
    {
        return ini_get('post_max_size');
    }

    public function getUploadMaxSize()
    {
        return ini_get('upload_max_filesize');
    }

    public function getDataMaxSize()
    {
        return min($this->getPostMaxSize(), $this->getUploadMaxSize());
    }
    
    public function getDataMaxSizeInBytes()
    {
        $iniSize = $this->getDataMaxSize();
        $size = substr($iniSize, 0, strlen($iniSize)-1);
        $parsedSize = 0;
        switch (strtolower(substr($iniSize, strlen($iniSize)-1))) {
            case 't':
                $parsedSize = $size*(1024*1024*1024*1024);
                break;
            case 'g':
                $parsedSize = $size*(1024*1024*1024);
                break;
            case 'm':
                $parsedSize = $size*(1024*1024);
                break;
            case 'k':
                $parsedSize = $size*1024;
                break;
            case 'b':
            default:
                $parsedSize = $size;
                break;
        }
        return $parsedSize;
    }
    	
	public function getUploaderUrl($url)
    {
        if (!is_string($url)) {
            $url = '';
        }
        $design = Mage::getDesign();
        $theme = $design->getTheme('skin');
        if (empty($url) || !$design->validateFile($url, array('_type' => 'skin', '_theme' => $theme))) {
            $theme = $design->getDefaultTheme();
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
            $design->getArea() . '/' . $design->getPackageName() . '/' . $theme . '/' . $url;
    }
	
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    } 
    
    protected function _prepareLayout()
    {
       $this->setChild(
            'delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(array(
                    'id'      => '{{id}}-delete',
                    'class'   => 'delete',
                    'type'    => 'button',
                    'label'   => Mage::helper('adminhtml')->__('Remove'),
                    'onclick' => $this->getJsObjectName() . '.removeFile(\'{{fileId}}\')'
                ))
        );

        return parent::_prepareLayout();
    } 
    
	public function getFeed(){
		
		if(Mage::registry('gomage_feed')){
        	return Mage::registry('gomage_feed');
        }else{
        	return  new Varien_Object();
        }
		
	}
	
	public static function getAttributeCollection(){
					
		$attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setItemObjectClass('catalog/resource_eav_attribute')
			->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());
					
		return $attribute_collection;
	}
	
	public static function getAttributeOptionsArray(){
			    							    
		$options = array();
		
		$options['Product Id'] = array('code'=>"entity_id", 'label' => "Product Id");
    	$options['Is In Stock'] = array('code'=>"is_in_stock" , 'label' =>  "Is In Stock");
    	$options['Qty'] = array('code'=>"qty" , 'label' =>  "Qty");
    	$options['Image'] = array('code'=>"image" , 'label' =>  "Image");
    	$options['URL'] = array('code'=>"url" , 'label' =>  "URL");
    	$options['URL (Parent product)'] = array('code'=>"parent_url" , 'label' =>  "URL (Parent product)");
    	$options['Category'] = array('code'=>"category", 'label' =>  "Category");
    	$options['Final Price'] = array('code'=>"final_price", 'label' =>  "Final Price");
    	$options['Store Price'] = array('code'=>"store_price", 'label' =>  "Store Price");    	
    	$options['Image 2'] = array('code'=>"image_2", 'label' =>  "Image 2");
    	$options['Image 3'] = array('code'=>"image_3", 'label' =>  "Image 3");
    	$options['Image 4'] = array('code'=>"image_4", 'label' =>  "Image 4");
    	$options['Image 5'] = array('code'=>"image_5", 'label' =>  "Image 5");
    	$options['Parent Base Image'] = array('code'=>"parent_base_image" , 'label' =>  "Parent Base Image");
    	$options['Category > SubCategory'] = array('code'=>"category_subcategory", 'label' => "Category > SubCategory");
    	$options['Parent SKU'] = array('code'=>"parent_sku", 'label' => "Parent SKU");
				
		$custom_attributes = Mage::getResourceModel('gomage_feed/custom_attribute_collection');
		
		foreach($custom_attributes as $attribute){
			
			$label = '* '.$attribute->getName();
			
			$options[$label] = array('code'=>sprintf('custom:%s', $attribute->getCode()), 'label'=>$label);
			
		}
				
		foreach(self::getAttributeCollection() as $attribute){
			if($attribute->getFrontendLabel()){
			$options[$attribute->getFrontendLabel()] = array('code'=>$attribute->getAttributeCode(), 'label'=>($attribute->getFrontendLabel() ? $attribute->getFrontendLabel() : $attribute->getAttributeCode()));
			}
			
		}
		
		ksort($options);
								
		return $options;
		
	}
	public static function getAttributeSelect($i, $current = null, $active = true){
		
		$options = array();
		
		$options[] = "<option value=''>Not Set</option>";
		
		foreach(self::getAttributeOptionsArray() as $attribute){
			
			extract($attribute);
			
			$selected = '';
			
			if($code == $current){
				$selected = 'selected="selected"';
			}
			
			$options[] = "<option value=\"{$code}\" {$selected}>{$label}</option>";
			
		}
		
		return '<select style="width:260px;display:'.($active ? 'block' : 'none').'" id="mapping-'.$i.'-attribute-value" name="field['.$i.'][attribute_value]">'.implode('', $options).'</select>';
		
	}
	
	public static function getSystemSections()
	{	    
	    $data = array(); 
	    
	    $fileDir = Mage::getBaseDir('media') . DS . 'productsfeed' . DS . 'examples';	    
    	if ($handle = opendir($fileDir)) 
    	{
            while (false !== ($dir = readdir($handle))) {                
                if ($dir != '.' && $dir != '..')
                {
                    if (is_dir($fileDir . DS . $dir) && ($sub_handle = opendir($fileDir . DS . $dir)))
                    {
                        $data[$dir] = array();
                        while (false !== ($file = readdir($sub_handle))) {  
                            if ($file != '.' && $file != '..') {
                                $data[$dir][] = $file;        
                            }
                        }
                        closedir($sub_handle);
                    }
                }                
            }        
            closedir($handle);
        }
        
        return $data;
        
	}
       
}