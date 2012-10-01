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
 * @since        Class available since Release 2.0
 */

class GoMage_Feed_Block_Adminhtml_Items_Mappingexportftp extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct(){
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'gomage_feed';
        $this->_controller = 'adminhtml_items';
        $this->_mode = 'mappingexportftp';
        
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_removeButton('save');
        
    }
    public function getBackUrl(){
    	
    	if($feed = Mage::registry('gomage_feed')){
    		return $this->getUrl('*/*/edit', array('id'=>$feed->getId(), 'tab'=>'content_section'));
    	}
    	return $this->getUrl('*/*');
    	
    }
    public function getHeaderText(){
    	
        return $this->__('Export Fields to Server');
        
    }
}