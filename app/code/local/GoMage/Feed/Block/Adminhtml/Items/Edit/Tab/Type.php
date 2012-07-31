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

class GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Type extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected function _toHtml(){
		
		return parent::_toHtml().'
			<script type="text/javascript">
			var templateSyntax = /(^|.|\r|\n)({{(\w+)}})/;
			function setSettings(urlTemplate, typeElement) {
		        var template = new Template(urlTemplate, templateSyntax);
		        
		        setLocation(template.evaluate({type:$F(typeElement)}));
		    }
			</script>';
		
	}
	
	protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $this->__('Continue'),
                    'onclick'   => "setSettings('".$this->getContinueUrl()."','type')",
                    'class'     => 'save'
                    ))
                );
        return parent::_prepareLayout();
    }
    
    public function getContinueUrl()
    {
        return $this->getUrl('*/*/new', array(
            '_current'  => true,
            'type'      => '{{type}}'
        ));
    }
	
	protected function _prepareForm(){
        
        $form = new Varien_Data_Form();
        
        $this->setForm($form);
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => $this->__('Settings')));
     
    	$fieldset->addField('type', 'select', array(
            'name'      => 'type',
            'label'     => $this->__('Feed Type'),
            'title'     => $this->__('Feed Type'),
            'required'  => true,
            'values'	=>array('csv'=>'CSV,TXT','xml'=>'XML'),
        ));
        
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
        
        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));
        
        return parent::_prepareForm();
        
    }
    
  
}