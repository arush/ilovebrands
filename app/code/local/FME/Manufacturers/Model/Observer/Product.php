<?php

class FME_Manufacturers_Model_Observer_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the manufacturers_id refers to the key field in your database table.
        $this->_init('manufacturers/manufacturers', 'manufacturers_id');
    }
	
	/**
	 * Inject one tab into the product edit page in the Magento admin
	 *
	 * @param Varien_Event_Observer $observer
	 */
	/*public function injectTabs(Varien_Event_Observer $observer)
	{
		$block = $observer->getEvent()->getBlock();
		
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
			if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
				$block->addTab('custom-product-tab-01', array(
					'label'     => 'Manufacturers',
					'content'   => $block->getLayout()->createBlock('adminhtml/template', 'custom-tab-content', array('template' => 'manufacturers/content.phtml'))->toHtml(),
				));
			}
		}
	}*/

	/**
	 * This method will run when the product is saved
	 * Use this function to update the product model and save
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function saveTabData(Varien_Event_Observer $observer)
	{
		if($post = $this->_getRequest()->getPost()){
			$attributeCode = Mage::helper('manufacturers')->getAttributeCode();
			if(isset($post['product'][$attributeCode]) && $post['product'][$attributeCode] != 0 && $post['product'][$attributeCode] != NULL ) {
				$eav_attribute_option = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option');
				$eav_attribute_option_value = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
				$con_read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$con_write = Mage::getSingleton('core/resource')->getConnection('core_write');
				
				$cmtsrowqry = "select eav_option_value.value, eav_option_value.option_id from ".$eav_attribute_option." eav_option left outer join ".$eav_attribute_option_value." eav_option_value on eav_option.option_id = eav_option_value.option_id where eav_option.option_id=".$post['product'][$attributeCode]." and eav_option_value.store_id = 0;";
				$cmtrowselect = $con_read->query($cmtsrowqry);
				$eav_collection = $cmtrowselect->fetchAll();
				$brand_id = Mage::helper('manufacturers')->checkExistingManufacturer($eav_collection[0]['option_id'],$eav_collection[0]['value']);
				if($brand_id==""){
					$brand_id = Mage::helper('manufacturers')->getBrandIdByName($eav_collection[0]['value']);
				}
				if($brand_id!="") {
					try {
						// Load the current product model	
						$product = Mage::registry('product');
						$condition = $this->_getWriteAdapter()->quoteInto('product_id = ?', $product["entity_id"]);
						$this->_getWriteAdapter()->delete($this->getTable('manufacturers_products'), $condition);	
						$productsArray = array();
						$productsArray['manufacturers_id'] = $brand_id;
						$productsArray['product_id'] = $product["entity_id"];
						$this->_getWriteAdapter()->insert($this->getTable('manufacturers_products'), $productsArray);				
					}
					 catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					}
				}
				// else remove all brands associated with that product before
			}else{
				$product = Mage::registry('product');
				$condition = $this->_getWriteAdapter()->quoteInto('product_id = ?', $product["entity_id"]);
				$this->_getWriteAdapter()->delete($this->getTable('manufacturers_products'), $condition);
			}
		}
	}
	
	/**
	 * Shortcut to getRequest
	 */
	protected function _getRequest()
	{
		return Mage::app()->getRequest();
	}
}
