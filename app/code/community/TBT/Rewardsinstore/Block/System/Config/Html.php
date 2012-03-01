<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_System_Config_Html extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = "";
        $html .= "
            <div style=\" margin-bottom: 12px; width: 430px;\">
            Sweet Tooth Instore v" . Mage::getConfig()->getNode('modules/TBT_Rewardsinstore/version') . 
            ". <a href='http://www.sweettoothrewards.com/news' target='_blank'>Click here for updates.</a><BR /> 
        ";
        
        $html .= Mage::getBlockSingleton('rewards/manage_widget_loyalty')->toHtml();
        
        $html .= "  </div> ";
        $html .= ""; //$this->_getFooterHtml($element);
        
        return $html;
    }
    
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('show_in_default' => 1, 'show_in_website' => 1));
        }
        return $this->_dummyElement;
    }
    
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }
    
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName; //TODO: move as property of form
        $data = isset($configData [$path]) ? $configData [$path] : array();
        
        $e = $this->_getDummyElement();
        
        $moduleKey = substr($moduleName, strpos($moduleName, '_') + 1);
        $ver = (Mage::getConfig()->getModuleConfig($moduleName)->version);
        
        if ($ver) {
            $field = $fieldset->addField($moduleName, 'label', array('name' => 'ssssss', 'label' => $moduleName, 'value' => $ver))->setRenderer($this->_getFieldRenderer());
            return $field->toHtml();
        }
        return '';
    }
}
