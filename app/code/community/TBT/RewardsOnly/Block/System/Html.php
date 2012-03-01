<?php
/**
 * WDCA
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   WDCA
 * @package    TBT_Enhancedgrid
 * @copyright  Copyright (c) 2008-2010 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 


class TBT_RewardsOnly_Block_System_Html
	extends TBT_Rewards_Block_System_Html
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		
		$html = "";
        $html .= "
        	<div style=\" margin-bottom: 12px; width: 430px;\">
            Sweet Tooth Points-only Extension v". Mage::getConfig()->getNode('modules/TBT_RewardsOnly/version')  .
            ". <a href='http://www.getsweettooth.com/news' target='_blank'>Click here for updates.</a><BR /> 
            </div>
        ";
        $html .= "";//$this->_getFooterHtml($element);

        return $html;
    }

}
