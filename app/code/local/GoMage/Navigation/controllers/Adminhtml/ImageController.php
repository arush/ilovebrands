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

class GoMage_Navigation_Adminhtml_ImageController extends Mage_Adminhtml_Controller_Action{
	
	public function uploadAction()
    {
    	
    	file_put_contents(Mage::getBaseDir('var').'/data.txt', print_r($_FILES, true));
    	
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('option_image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            //$uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );

            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }
		
		file_put_contents(Mage::getBaseDir('var').'/data-response.txt', print_r($result, true));
		
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}