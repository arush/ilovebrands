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

class GoMage_Feed_Block_Adminhtml_Items_Edit_Tab_Advanced extends Mage_Adminhtml_Block_Widget_Form
{
	
	
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        
        if(Mage::registry('gomage_feed')){
        	$item = Mage::registry('gomage_feed');
        }else{
        	$item = Mage::getModel('gomage_feed/item');
        }
        
        $this->setForm($form);
        $fieldset = $form->addFieldset('advanced', array('legend' => $this->__('File Creation Settings')));
        
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
        
        
        $field = $fieldset->addField('iteration_limit', 'text', array(
            'name'      => 'iteration_limit',
            'label'     => $this->__('Number of Products'),
            'title'     => $this->__('Number of Products'),
            'required'  => true,
            'note'		=> '"0" = All products. <br/>This option allows to optimize file creation for low memory servers.<br/>You have to increase php memory_limit before changing this value to maximum.',
            
        ));
        if(!$item->getId()){        
        	$field ->setValue('0');        
        }
        
        $field = $fieldset->addField('use_layer', 'select', array(
            'name'      => 'use_layer',
            'label'     => $this->__('Export Out of Stock Products'),
            'title'     => $this->__('Export Out of Stock Products'),
            'required'  => false,
            'values'	=> array(1=>$this->__('No'),0=>$this->__('Yes')),            
        	));                
        if(!$item->getId()){        
        	$field ->setValue('1');        
        }
                
        $field = $fieldset->addField('use_disabled', 'select', array(
            'name'      => 'use_disabled',
            'label'     => $this->__('Export Disabled Products'),
            'title'     => $this->__('Export Disabled Products'),
            'required'  => false,
            'values'	=> array(1=>$this->__('No'),0=>$this->__('Yes'))
        	));                
        if(!$item->getId()){        
        	$field ->setValue('1');        
        }
        
        $options = Mage_Catalog_Model_Product_Visibility::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>$this->__('Not Use Option')));
        array_push($options, array('value'=>'5', 'label'=>$this->__('Only Catalog')));
        array_push($options, array('value'=>'6', 'label'=>$this->__('Only Search')));
        
    	$field = $fieldset->addField('visibility', 'select', array(
            'name'      => 'visibility',
            'label'     => $this->__('Products Visibility'),
            'title'     => $this->__('Products Visibility'),
            'required'  => false,
            'values'	=> $options
        	));                
        if(!$item->getId()){        
        	$field ->setValue('4');        
        }
        
        $fieldset = $form->addFieldset('upload_settings', array('legend' => $this->__('Generate and Upload Settings')));
        
        $headerBar = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Feed Pro Help'),
                    'class'   => 'go',
                    'id'      => 'feed_pro_help_2',
                    'onclick' => 'window.open(\'http://www.gomage.com/faq/extensions/feed-pro\')'
                ));
        
        $fieldset->setHeaderBar(
                    $headerBar->toHtml()
                ); 
        
        /*        
        $fieldset->addField('upload_label', 'note', array(
            'name'      => 'upload_label',
            'label'     => $this->__('I set "Upload time" for Cron but it doesn\'t work.'),                        
            'text'      => 'Please, check the Cron settings on a server. You need to set up your Cron for every 5 minutes.
						    Learn more at &nbsp;<a target="_blank" href="http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/how_to_setup_a_cron_job#unixbsdlinux">unixbsdlinux</a>'
        ));
        
        $fieldset->addField('upload_label_2', 'note', array(
            'name'      => 'upload_label_2',
            'label'     => $this->__('The feed on one of my stores does not extract all my products.'),                        
            'text'      => 'Set automatic feed generation for different times for each feed. Navigate to Advanced Settings -> "Upload Settings" and set additional days and / or times.
							Setting run times 2 hours apart will help resolve the issue. By having a gap between runs, you are insured that any variables from the first run is complete before the second run. Running both together exceeds memory capacity which results in the feed failing to extract all products.'
        ));
        */
         
        $field = $fieldset->addField('upload_day', 'multiselect', array(
            'name'      => 'upload_day',
            'label'     => $this->__('Available Days'),
            'title'     => $this->__('Available Days'),
            'required'  => false,
            'values'	=> array(
            	array('label'=>$this->__('Sunday'), 'value'=>'sun'),
            	array('label'=>$this->__('Monday'), 'value'=>'mon'),
            	array('label'=>$this->__('Tuesday'), 'value'=>'tue'),
            	array('label'=>$this->__('Wednesday'), 'value'=>'wed'),
            	array('label'=>$this->__('Thursday'), 'value'=>'thu'),
            	array('label'=>$this->__('Friday'), 'value'=>'fri'),
            	array('label'=>$this->__('Saturday'), 'value'=>'sat'),
            )
        ));
        
        if(!$item->getId()){
        
        	$field ->setValue('sun,mon,tue,wed,thu,fri,sat');
        
        }
        
        $hours = array();
        
        $locale = Mage::getSingleton('core/locale');
        
        
        for($i = 0;$i<24;$i++){
        	
        	$hours[] = array('label'=>sprintf('%02d:00',$i), 'value'=>date('H', mktime($i, 0, 0, 1, 1, 1970)+$locale->date()->getGmtOffset()));
        	
        }
        
        
        $fieldset->addField('upload_hour', 'select', array(
            'name'      => 'upload_hour',
            'label'     => $this->__('Active From, hour'),
            'title'     => $this->__('Active From, hour'),
            'required'  => false,
            'values'	=> $hours
        ));
        
        $fieldset->addField('upload_hour_to', 'select', array(
            'name'      => 'upload_hour_to',
            'label'     => $this->__('Active To, hour'),
            'title'     => $this->__('Active To, hour'),
            'required'  => false,
            'values'	=> $hours,
            'disabled'  => (!$item->getId() || $item->getData('upload_interval') > 6)
        ));
        
        $field = $fieldset->addField('upload_interval', 'select', array(
            'name'      => 'upload_interval',
            'label'     => $this->__('Interval, hours'),
            'title'     => $this->__('Interval, hours'),
            'required'  => false,
            'values'	=> array(
            	
            	array('label'=>$this->__('every 1 hour'), 'value'=>1),
            	array('label'=>$this->__('every 3 hours'), 'value'=>3),
            	array('label'=>$this->__('every 6 hours'), 'value'=>6),
            	array('label'=>$this->__('every 12 hours'), 'value'=>12),
            	array('label'=>$this->__('every 24 hours'), 'value'=>24),
            	
            ),
            'class'     => 'gomage-feed-validate-interval'
        ));
        if(!$item->getId()){
        
        	$field ->setValue('24');
        
        }
        $field->setOnchange('gomagefeed_setinterval(this, \'upload_hour_to\')'); 
                
        $field = $field = $fieldset->addField('restart_cron', 'select', array(
            'name'      => 'restart_cron',
            'label'     => $this->__('Restart Cron, times'),
            'title'     => $this->__('Restart Cron, times'),
            'required'  => false,
            'values'	=> array(            	
            	array('label'=>$this->__('1'), 'value'=>1),
            	array('label'=>$this->__('2'), 'value'=>2),
            	array('label'=>$this->__('3'), 'value'=>3),
            	array('label'=>$this->__('4'), 'value'=>4),
            	array('label'=>$this->__('5'), 'value'=>5),            	
            )
        ));
        
        if(!$item->getId()){
        
        	$field ->setValue('3');
        
        }
        
        if($item->getId()){
        
        	$form->setValues($item->getData());
        
        }
                
        return parent::_prepareForm();
        
    }
    
  
}