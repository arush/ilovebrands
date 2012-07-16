<?php

/**
 * Renderer for SagePay banner in System Configuration
 * @author      Ebizmart Team <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'sagepaysuite/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getSagePaySuiteVersion()
    {
    	return (string) Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/version');
    }

    public function getCheckExtensions()
    {
    	return array(
    					'iconv',
						'curl',
						'mbstring',
    				);
    }

    public function getPxParams() {

		$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/version');
		$ext = "Sage Pay Suite PRO;{$v}";

		$modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
		$aux = (array_key_exists('Enterprise_Enterprise', $modulesArray))? 'EE' : 'CE' ;
		$mageVersion = Mage::getVersion();
		$mage = "Magento {$aux};{$mageVersion}";

		$hash = md5($ext . '_' . $mage . '_' . $ext);

    	return "ext=$ext&mage={$mage}&ctrl={$hash}";

    }

}
