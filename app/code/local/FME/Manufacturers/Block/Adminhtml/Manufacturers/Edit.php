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

class FME_Manufacturers_Block_Adminhtml_Manufacturers_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	
	protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
		return parent::_prepareLayout();
	}
	
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'manufacturers';
        $this->_controller = 'adminhtml_manufacturers';
        
        $this->_updateButton('save', 'label', Mage::helper('manufacturers')->__('Save Manufacturer'));
        $this->_updateButton('delete', 'label', Mage::helper('manufacturers')->__('Delete Manufacturer'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('manufacturers_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'manufacturers_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'manufacturers_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('manufacturers_data') && Mage::registry('manufacturers_data')->getId() ) {
            return Mage::helper('manufacturers')->__("Edit Manufacturer '%s'", $this->htmlEscape(Mage::registry('manufacturers_data')->getTitle()));
        } else {
            return Mage::helper('manufacturers')->__('Add Manufacturer');
        }
    }
}