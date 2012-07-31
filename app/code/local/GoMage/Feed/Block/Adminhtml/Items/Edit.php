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

class GoMage_Feed_Block_Adminhtml_Items_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct(){
    	
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'gomage_feed';
        $this->_controller = 'adminhtml_items';
        
        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));
		
		$feed = Mage::registry('gomage_feed');
		
		if($feed && $feed->getId() > 0){
			
			$this->_addButton('generate', array(
	            'label'     => $this->__('Generate File'),
	            'onclick'   => 'if($(\'loading-mask\')){$(\'loading-mask\').show();}setLocation(\''.$this->getUrl('*/*/generate', array('id'=>$feed->getId())).'\')',
	        ), -100);
	        
	        if($feed->getFtpActive()){
	        
		        $this->_addButton('upload', array(
		            'label'     => $this->__('Upload File'),
		            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/upload', array('id'=>$feed->getId())).'\')',
		        ), -100);
		        
	        }
        
        }
		
        $this->_addButton('saveandcontinue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $_data = array();
        $_data['data'] = GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Content_Csv::getSystemSections(); 
        $_data['url'] = $this->getUrl('*/*/mappingimportsection', array('id'=>($feed && $feed->getId() ? $feed->getId() : 0)));
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            var GomageFeedAdmin = new GomageFeedAdminSettings(" . Zend_Json::encode($_data) . ");

        ";
        
        if ($this->getRequest()->getActionName() == 'new' &&
        	!$this->getRequest()->getParam('type')){
        	$this->removeButton('save');
        	$this->removeButton('saveandcontinue');
        }
        
    }
    
    public function getHeaderText(){
    	
        if( Mage::registry('gomage_feed') && Mage::registry('gomage_feed')->getId() ) {
            return $this->__("Edit %s", $this->htmlEscape(Mage::registry('gomage_feed')->getName()));
        } else {
            return $this->__('Add Item');
        }
    }
}