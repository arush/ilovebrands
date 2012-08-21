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

class GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Ftp extends Mage_Adminhtml_Block_Widget_Form
{
	
	
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        
        if(Mage::registry('gomage_feed')){
        	$item = Mage::registry('gomage_feed');
        }else{
        	$item = new Varien_Object();
        }
        
        $this->setForm($form);
        $fieldset = $form->addFieldset('ftp_fieldset', array('legend' => $this->__('FTP Settings')));
        
        $headerBar = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Feed Pro Help'),
                    'class'   => 'go',
                    'id'      => 'feed_pro_help',
                    'onclick' => 'window.open(\'http://www.gomage.com/faq/extensions/feed-pro\')'
                ));
        
        $fieldset->setHeaderBar(
                    $headerBar->toHtml()
                ); 
        
        
        $fieldset->addField('ftp_active', 'select', array(
            'name'      => 'ftp_active',
            'label'     => $this->__('Enabled'),
            'title'     => $this->__('Enabled'),
            'required'  => false,
            'values'	=> array($this->__('No'), $this->__('Yes'))
        ));
        
    	$fieldset->addField('ftp_host', 'text', array(
            'name'      => 'ftp_host',
            'label'     => $this->__('Host Name'),
            'title'     => $this->__('Host Name'),
            'required'  => false,
			'note'	=> $this->__('e.g. "ftp.domain.com"<br/>FTP Server uses port #21 by default. <br/>Use "domain.com:portnumber" to change the port.'),
        ));
        $fieldset->addField('ftp_user_name', 'text', array(
            'name'      => 'ftp_user_name',
            'label'     => $this->__('User Name'),
            'title'     => $this->__('User Name'),
            'required'  => false,
        ));
        $fieldset->addField('ftp_user_pass', 'password', array(
            'name'      => 'ftp_user_pass',
            'label'     => $this->__('Password'),
            'title'     => $this->__('Password'),
            'required'  => false,
        ));
        $fieldset->addField('ftp_dir', 'text', array(
            'name'      => 'ftp_dir',
            'label'     => $this->__('Path'),
            'title'     => $this->__('Path'),
            'required'  => false,
            'note'	=> $this->__('e.g. "/yourfolder"'),
        ));
        
        $fieldset->addField('ftp_passive_mode', 'select', array(
            'name'      => 'ftp_passive_mode',
            'label'     => $this->__('Passive mode'),
            'title'     => $this->__('Passive mode'),
            'required'  => false,
            'values'	=> array($this->__('Disable'), $this->__('Enable'))
        ));
        
        
        $form->setValues($item->getData());
        
        
        return parent::_prepareForm();
        
    }
    
  
}