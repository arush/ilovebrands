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

class FME_Manufacturers_Block_Adminhtml_Manufacturers_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('manufacturers_form', array('legend'=>Mage::helper('manufacturers')->__('Manufacturer information')));
		
		$fieldset->addField('m_name', 'text', array(
		  'label'     => Mage::helper('manufacturers')->__('Name'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'm_name',
		));
		
		$fieldset->addField('identifier', 'text', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('manufacturers')->__('SEF URL Identifier'),
            'title'     => Mage::helper('manufacturers')->__('SEF URL Identifier'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('manufacturers')->__('(eg: domain.com/identifier)') . '</small></p>',
        ));
		
		$fieldset->addField('m_website', 'text', array(
		  'label'     => Mage::helper('manufacturers')->__('Website'),
		  'required'  => false,
		  'name'      => 'm_website',
		));
		
		
		$fieldset->addField('m_featured', 'select', array(
		  'label'     => Mage::helper('manufacturers')->__('Make this Featured'),
		  'name'      => 'm_featured',
		  'values'    => array(
			  array(
				  'value'     => '1',
				  'label'     => Mage::helper('manufacturers')->__('Yes'),
			  ),
		
			  array(
				  'value'     => '0',
				  'label'     => Mage::helper('manufacturers')->__('No'),
			  ),
		  ),
		));
		
		$fieldset->addField('m_address', 'editor', array(
		  'name'      => 'm_address',
		  'label'     => Mage::helper('manufacturers')->__('Address'),
		  'title'     => Mage::helper('manufacturers')->__('Address'),
		  'style'     => 'width:275px; height:100px;',
		  'wysiwyg'   => false,
		  'required'  => false,
		));
		
		
		
		$fieldset->addField('store_id','multiselect',array(
		'name'      => 'stores[]',
		'label'     => Mage::helper('manufacturers')->__('Store View'),
		'title'     => Mage::helper('manufacturers')->__('Store View'),
		'required'  => true,
		'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
		));
		
		$fieldset->addField('status', 'select', array(
		  'label'     => Mage::helper('manufacturers')->__('Status'),
		  'name'      => 'status',
		  'values'    => array(
			  array(
				  'value'     => 1,
				  'label'     => Mage::helper('manufacturers')->__('Enabled'),
			  ),
		
			  array(
				  'value'     => 2,
				  'label'     => Mage::helper('manufacturers')->__('Disabled'),
			  ),
		  ),
		));
		
		$fieldset->addField('prid', 'hidden', array(
          	'label'     => Mage::helper('manufacturers')->__(''),
          	'name'      => 'prid',
	  		'id'      => 'prid',
      	));
		
		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
             array('tab_id' => 'form_section')
        );
	
        //make Wysiwyg Editor integrate in the form
        $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
        $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
        $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
        $wysiwygConfig["widget_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index');
		$wysiwygConfig["files_browser_window_width"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width');
		$wysiwygConfig["files_browser_window_height"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height');
        $plugins = $wysiwygConfig->getData("plugins");
        $plugins[0]["options"]["url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
        $plugins[0]["options"]["onclick"]["subject"] = "MagentovariablePlugin.loadChooser('".Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin')."', '{{html_id}}');";
        $plugins = $wysiwygConfig->setData("plugins",$plugins);
		
		$fieldset->addField('m_details', 'editor', array(
		  'name'      => 'm_details',
		  'label'     => Mage::helper('manufacturers')->__('About Manufacturer'),
		  'title'     => Mage::helper('manufacturers')->__('About Manufacturer'),
		  'style'     => 'height:20em; width:50em;',
		  'required'  => false,
		  'config'    => $wysiwygConfig
		));
		
		try {
			$object = Mage::getModel('manufacturers/manufacturers')->load( $this->getRequest()->getParam('id') );
			$note = false;
			$img = '';
						
			if( $object->getMLogo() ) {
				if($object->getMLogo() != "" && $object->getMLogo() != NULL) {
					$imageFile =  $object->getMLogo();				
					If ($imageFile) {
						$img = '<img src="' . Mage::helper('manufacturers')->getResizedUrl($imageFile,75,75,Mage::helper('manufacturers')->getLogoBackgroundColor()) . '" border="0" align="center"/>';
					} 
				} 
			} else {
					$str = "manufacturers/files/n/i/no_image_available.jpg";			
					$imge = ltrim(rtrim($str));
					$img = '<img src="' . Mage::helper('manufacturers')->getResizedUrl($str,75,75,Mage::helper('manufacturers')->getLogoBackgroundColor()) . '" border="0" align="center"/>';
			}
			
		} catch (Exception $e) {
			$str = "manufacturers/files/n/i/no_image_available.jpg";			
			$imge = ltrim(rtrim($str));
			$img = '<img src="' . Mage::helper('manufacturers')->getResizedUrl($str,75,75,Mage::helper('manufacturers')->getLogoBackgroundColor()) . '" border="0" align="center"/>';
		}
		
		$fieldset->addField('my_file_uploader', 'file', array(
			'label'        => Mage::helper('manufacturers')->__('Logo'),
			'note'      => $note,
			'name'        => 'my_file_uploader',
			'class'     =>  '',
			'required'  => false,
			'after_element_html' => $img,
   		 )); 
		
		$fieldset->addField('m_logo_thumb', 'hidden', array(
		  'label'     => Mage::helper('manufacturers')->__(''),
		  'name'      => 'm_logo_thumb',
		  'id'      => 'm_logo_thumb',
		  
		));
		
		$fieldset->addField('old_products', 'hidden', array(
          	'label'     => Mage::helper('manufacturers')->__('old_products'),
          	'name'      => 'old_products',
          	'id'   => 'old_products'
      	)); 
		
		$fieldset->addField('my_file', 'hidden', array(
        	'name'        => 'my_file',
    	));
		
		$mcollection = Mage::getModel('manufacturers/manufacturers')->getProductsCount($this->getRequest()->getParam('id'))->getData();
		$mcount = $mcollection[0]["products_ids"];
     
      if ( Mage::getSingleton('adminhtml/session')->getManufacturersData() )
      {
	  		$data = Mage::getSingleton('adminhtml/session')->getManufacturersData();
			$data['old_products'] = $mcount;
          $form->setValues($data);
          Mage::getSingleton('adminhtml/session')->setManufacturersData(null);
      } elseif ( Mage::registry('manufacturers_data') ) {
	  		$data = Mage::registry('manufacturers_data')->getData();
			$data['old_products'] = $mcount;
          $form->setValues($data);
      }
      return parent::_prepareForm();
  }
}