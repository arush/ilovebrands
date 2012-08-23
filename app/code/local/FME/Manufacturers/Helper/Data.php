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

class FME_Manufacturers_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_LIST_PAGE_TITLE					=	'manufacturers/manufacturers/page_title';
	const XML_PATH_LIST_MAIN_HEADING		    	=	'manufacturers/manufacturers/main_heading';
	const XML_PATH_LIST_IDENTIFIER					=	'manufacturers/manufacturers/identifier';
	const XML_PATH_LIST_ITEMS_PER_PAGE				=	'manufacturers/manufacturers/items_per_page';
	const XML_PATH_LIST_LIMIT_DESCRIPTION			=	'manufacturers/manufacturers/limit_description';
	const XML_PATH_LIST_META_DESCRIPTION			=	'manufacturers/manufacturers/meta_description';
	const XML_PATH_LIST_META_KEYWORDS				=	'manufacturers/manufacturers/meta_keywords';
	const XML_PATH_LIST_SHOW_LOGO					=	'manufacturers/manufacturers/show_manufacturers_logo';
	const XML_PATH_LIST_LOGO_WIDTH					=	'manufacturers/manufacturers/logo_width';
	const XML_PATH_LIST_LOGO_HEIGHT					=	'manufacturers/manufacturers/logo_height';
	const XML_PATH_LIST_LOGO_BG_COLOR				=	'manufacturers/manufacturers/logo_background_Color';
	const XML_PATH_LIST_SHOW_ALPHABET_HEADING		=	'manufacturers/manufacturers/show_manufacturers_with_alphabets';
	const XML_PATH_LIST_SHOW_VIEW_BY				=	'manufacturers/manufacturers/show_manufacturers_view_by';
	
	const XML_PATH_SEO_URL_SUFFIX					=	'manufacturers/seo/url_suffix';
	
	const XML_PATH_IMPORTFROMMAGENTO_ATTRIBUTE_CODE	=	'manufacturers/importfrommagento/attribute_code';

	const XML_PATH_LIST_PRODUCT_PAGE_LOGO_HEADING	=	'manufacturers/productpagelogo/product_page_logo_heading';
	const XML_PATH_PRODUCT_PAGE_LOGO_WIDTH			=	'manufacturers/productpagelogo/product_logo_width';
	const XML_PATH_PRODUCT_PAGE_LOGO_HEIGHT			=	'manufacturers/productpagelogo/product_logo_height';
	const XML_PATH_PRODUCT_PAGE_LOGO_BG_COLOR		=	'manufacturers/productpagelogo/logo_background_Color';
	const XML_PATH_PRODUCT_PAGE_BRAND_LINK			=	'manufacturers/productpagelogo/product_page_brand_link';
	
	const XML_PATH_FEATURED_MANUFACTURERS_TITLE		=	'manufacturers/featuredmanufacturers/main_heading';
	const XML_PATH_FEATURED_MANUFACTURERS_WIDTH		=	'manufacturers/featuredmanufacturers/width';
	const XML_PATH_FEATURED_MANUFACTURERS_HEIGHT		=	'manufacturers/featuredmanufacturers/height';
	const XML_PATH_FEATURED_MANUFACTURERS_LOGO_WIDTH	=	'manufacturers/featuredmanufacturers/logo_width';
	const XML_PATH_FEATURED_MANUFACTURERS_LOGO_HEIGHT	=	'manufacturers/featuredmanufacturers/logo_height';
	const XML_PATH_FEATURED_MANUFACTURERS_LOGO_BG_COLOR	=	'manufacturers/featuredmanufacturers/logo_background_Color';
	
	const XML_PATH_MANUFACTURERS_SORT			=	'manufacturers/manufacturers/sort_manufacturers';
	const XML_PATH_MANUFACTURERS_ORDER			=	'manufacturers/manufacturers/order_manufacturers_by';
	const XML_PATH_MANUFACTURERS_LEFT_SORT			=	'manufacturers/leftnav/sort_left_manufacturers';
	const XML_PATH_MANUFACTURERS_LEFT_ORDER			=	'manufacturers/leftnav/order_left_manufacturers_by';
	const XML_PATH_FEATURED_SORT				=	'manufacturers/featuredmanufacturers/sort_featured_manufacturers';
	const XML_PATH_FEATURED_ORDER				=	'manufacturers/featuredmanufacturers/order_featured_manufacturers_by';

	public function getManufacturerSort()
	{
		return Mage::getStoreConfig(self::XML_PATH_MANUFACTURERS_SORT);
	}
	
	public function getManufacturerOrder()
	{
		return Mage::getStoreConfig(self::XML_PATH_MANUFACTURERS_ORDER);
	}
	
	public function getLeftManufacturerSort()
	{
		return Mage::getStoreConfig(self::XML_PATH_MANUFACTURERS_LEFT_SORT);
	}
	
	public function getLeftManufacturerOrder()
	{
		return Mage::getStoreConfig(self::XML_PATH_MANUFACTURERS_LEFT_ORDER);
	}
	
	public function getFeaturedManufacturerSort()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_SORT);
	}
	
	public function getFeaturedManufacturerOrder()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_ORDER);
	}
	
	public function getListPageTitle()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_PAGE_TITLE);
	}
	
	public function getShowLogo() 
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_SHOW_LOGO);
	}
	
	public function getShowAlphaHeading() 
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_SHOW_ALPHABET_HEADING);
	}
	
	public function getShowViewBy() 
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_SHOW_VIEW_BY);
	}
	
	public function getMainHeadingText()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_MAIN_HEADING);
	}
	
	public function getBrandLinks()
	{
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PAGE_BRAND_LINK);
	}
	
	public function getProductPageLogoText()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_PRODUCT_PAGE_LOGO_HEADING);
	}
	
	public function getListIdentifier()
	{
		$identifier = Mage::getStoreConfig(self::XML_PATH_LIST_IDENTIFIER);
		if ( !$identifier ) {
			$identifier = 'manufacturers';
		}
		return $identifier;
	}
	
	public function geturlIdentifier()
	{
		$identifier = $this->getListIdentifier() . Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
		return $identifier;
	}
	
	public function getListItemsPerPage()
	{
		return (int)Mage::getStoreConfig(self::XML_PATH_LIST_ITEMS_PER_PAGE);
	}
	
	public function getListLimitDescription()
	{
		return (int)Mage::getStoreConfig(self::XML_PATH_LIST_LIMIT_DESCRIPTION);
	}
	
	public function getListMetaDescription()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_DESCRIPTION);
	}
	
	public function getListMetaKeywords()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_KEYWORDS);
	}
	
	public function getLogoWidth()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_LOGO_WIDTH);
	}
	public function getLogoHeight()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_LOGO_HEIGHT);
	}
	public function getLogoBackgroundColor()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_LOGO_BG_COLOR);
	}
	
	
	public function getProductPageLogoWidth()
	{
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PAGE_LOGO_WIDTH);
	}
	public function getProductPageLogoHeight()
	{
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PAGE_LOGO_HEIGHT);
	}
	public function getProductPageLogoBackgroundColor()
	{
		return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PAGE_LOGO_BG_COLOR);
	}
	
	public function getUrl($identifier = null)
	{
		
		if ( is_null($identifier) ) {
			$url = Mage::getUrl('') . self::getListIdentifier() . self::getSeoUrlSuffix();
		} else {
			$url = Mage::getUrl('') . $identifier . self::getSeoUrlSuffix();
		}

		return $url;
		
	}
	public function getSeoUrlSuffix()
	{
		return Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
	}
	
	public function getAttributeCode()
	{
		return Mage::getStoreConfig(self::XML_PATH_IMPORTFROMMAGENTO_ATTRIBUTE_CODE);
	}
	
	public function getFeaturedBlockTitle()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_TITLE);
	}
	
	public function getFeaturedBlockWidth()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_WIDTH);
	}
	
	public function getFeaturedBlockHeight()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_HEIGHT);
	}
	
	public function getFeaturedBlockLogoWidth()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_LOGO_WIDTH);
	}
	
	public function getFeaturedBlockLogoHeight()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_LOGO_HEIGHT);
	}
	
	public function getFeaturedBlockLogoBackgroundColor()
	{
		return Mage::getStoreConfig(self::XML_PATH_FEATURED_MANUFACTURERS_LOGO_BG_COLOR);
	}
	
	
	public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }

    public function extensionEnabled($extension_name)
	{
		$modules = (array)Mage::getConfig()->getNode('modules')->children();
		if (!isset($modules[$extension_name])
			|| $modules[$extension_name]->descend('active')->asArray()=='false'
			|| Mage::getStoreConfig('advanced/modules_disable_output/'.$extension_name)
		) return false;
		return true;
	}
	
	/**
	 * Returns the resized Image URL
	 *
	 * @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
	 * @param int $x Width
	 * @param int $y Height
	 */
	public function getResizedUrl($imgUrl,$x=100,$y=100,$color){


		$imgPath=$this->splitImageValue($imgUrl,"path");
		$imgName=$this->splitImageValue($imgUrl,"name");

		/**
		 * Path with Directory Seperator
		 */
		$imgPath=str_replace("/",DS,$imgPath);

		/**
		 * Absolute full path of Image
		 */
		
		 $imgPathFull=Mage::getBaseDir("media").DS.$imgPath.DS.$imgName;
		 

		/**
		 * If Y is not set set it to as X
		 */
		$width=$x;
		$y?$height=$y:$height=$x;

		/**
		 * Resize folder is widthXheight
		 */
		$resizeFolder=$width."X".$height;

		/**
		 * Image resized path will then be
		 */
		 
		$imageResizedPath = Mage::getBaseDir("media").DS.$imgPath.DS.$resizeFolder.DS.$imgName;
		
		/**
		 * First check in cache i.e image resized path
		 * If not in cache then create image of the width=X and height = Y
		 */
		 
		$colorArray = array();
		$colorArray = explode(",",$color);
		if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
			
			$imageObj = new Varien_Image($imgPathFull);
			$imageObj->constrainOnly(TRUE);
			$imageObj->keepAspectRatio(TRUE);
			$imageObj->keepFrame(TRUE);
			$imageObj->keeptransparency(FALSE);
			$imageObj->backgroundColor(array(intval($colorArray[0]),intval($colorArray[1]),intval($colorArray[2])));
			$imageObj->resize($width, $height);
			$imageObj->save($imageResizedPath);
		endif;

		/**
		 * Else image is in cache replace the Image Path with / for http path.
		 */
		$imgUrl=str_replace(DS,"/",$imgPath);

		/**
		 * Return full http path of the image
		 */
		return Mage::getBaseUrl("media").$imgUrl."/".$resizeFolder."/".$imgName;
	}

	/**
	 * Splits images Path and Name
	 *
	 * Path=custom/module/images/
	 * Name=example.jpg
	 *
	 * @param string $imageValue
	 * @param string $attr
	 * @return string
	 */
	public function splitImageValue($imageValue,$attr="name"){
		$imArray=explode("/",$imageValue);

		$name=$imArray[count($imArray)-1];
		$path=implode("/",array_diff($imArray,array($name)));
		if($attr=="path"){
			return $path;
		}
		else
			return $name;

	}
	
	public function getWysiwygFilter($data)
	{

		$helper = Mage::helper('cms');
		$processor = $helper->getPageTemplateProcessor();
		return $processor->filter($data);
	}
	
	
	public function getAttributeUniqueValues($attrCode)
	{
		
		// specify the attribute code
		$attributeCode = $attrCode;
		
		// get attribute model by attribute code, e.g. 'color'
		$attributeModel = Mage::getSingleton('eav/config')
				->getAttribute('catalog_product', $attributeCode);
		
		// build select to fetch used attribute value id's
		$select = Mage::getSingleton('core/resource')
				->getConnection('default_read')->select()
				->from($attributeModel->getBackendTable(), 'value')
				->where('attribute_id=?', $attributeModel->getId())
				->distinct();
		
		// read used values from the db
		$usedAttributeValues = Mage::getSingleton('core/resource')
				->getConnection('default_read')
				->fetchCol($select);
		
		// map used id's to the value labels using the source model
		if ($attributeModel->usesSource())
		{
			$usedAttributeValues = $attributeModel->getSource()->getOptionText(
				implode(',', $usedAttributeValues)
			);
		}
		
		return $usedAttributeValues;
		
	}
	
	public function getAttributeProducts($attributeValue) {
		
		$productStr = '';
		$collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter($attributeCode, 'SeaSense');
		foreach($collection as $_product){
			$productStr .= $_product->getId().",";
		} 
		$productArray = substr($productStr,0,-1);
		
		return $productArray;
	}
	
	// get manufacturer id using option id
	public function getManuId($option_id){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$select_id = 'SELECT manufacturers_id FROM '.$manufacturer_Table.' where option_id = '.$option_id." order by manufacturers_id desc limit 1";
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid=$last_id[0]['manufacturers_id'];
		return $mlasid;
	}
	
	// get manufacturer option_id
	public function getBrandsOptionId($manufacturers_id){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$select_id = 'SELECT * FROM '.$manufacturer_Table.' where manufacturers_id = '.$manufacturers_id;
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid= $last_id[0]['option_id'];
		return $mlasid;
	}
	
	// get manufacturer option_id
	public function geBrandsOptionId($manufacturers_id){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$select_id = 'SELECT * FROM '.$manufacturer_Table.' where manufacturers_id = '.$manufacturers_id;
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid= $last_id[0]['option_id'];
		return $mlasid;
	}
	
	// get manufacturer value
	public function getBrandName($manufacturers_id){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$select_name = 'SELECT m_name FROM '.$manufacturer_Table.' where manufacturers_id = '.$manufacturers_id;
		$qry_result=$read_qry->query($select_name);
		$manufact_name = $qry_result->fetchAll();
		$m_name=$manufact_name[0]['m_name'];
		return $m_name;
	}
	
	// get last inserted manufacturer id
	public function getLastBrandId(){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$pqry = "SELECT manufacturers_id FROM ".$manufacturer_Table." order by manufacturers_id desc limit 1";
		$selectp = $read_qry->query($pqry);
		$options = $selectp->fetchAll();
		return $options[0]['manufacturers_id'];
	}
	
	// get last inserted manufacturer id
	public function geLastBrandId(){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$pqry = "SELECT manufacturers_id FROM ".$manufacturer_Table." order by manufacturers_id desc limit 1";
		$selectp = $read_qry->query($pqry);
		$options = $selectp->fetchAll();
		return $options[0]['manufacturers_id'];
	}
	
	// get manufacturer id with name
	public function getBrandIdByName($brand_name){
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$select_id = "SELECT manufacturers_id FROM ".$manufacturer_Table." where m_name = '".$brand_name."'";
		$qry_result=$read_qry->query($select_id);
		$last_id = $qry_result->fetchAll();
		$mlasid=$last_id[0]['manufacturers_id'];
		return $mlasid;
	}
	
	 public function checkExistingManufacturer($option_id, $option_value)
    {
		$remove = array();
		$remove[] = "'";
		$remove[] = '"';

		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read_qry = Mage::getSingleton('core/resource')->getConnection('core_read');
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$ccm = $resource->getTableName('manufacturers');
		$manufacturer_Table = Mage::getSingleton('core/resource')->getTableName('manufacturers');
		$manufacturer_storeTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_store');
		$manufacturer_productsTable = Mage::getSingleton('core/resource')->getTableName('manufacturers_products');
		
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
		
		$mid 	= str_replace($remove, "", $option_id);
		$mname	= str_replace($remove, "", $option_value);
		 
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
			return $mlasid;
		}
    }
	
}
