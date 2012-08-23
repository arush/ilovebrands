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

class FME_Manufacturers_Model_Layouts{
    
	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
            $layouts = array();
			$node = Mage::getConfig()->getNode('global/cms/layouts') ? Mage::getConfig()->getNode('global/cms/layouts') : Mage::getConfig()->getNode('global/page/layouts');
			
			foreach ($node->children() as $layoutConfig) {
				$this->_options[] = array(
				   'value'=>(string)$layoutConfig->template,
				   'label'=>(string)$layoutConfig->label
				);
			}
			
		}
        return $this->_options;
    }
}
