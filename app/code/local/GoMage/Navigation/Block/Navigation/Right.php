<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 1.0
 */

class GoMage_Navigation_Block_Navigation_Right extends Mage_Core_Block_Template
{
        
    protected function _prepareLayout()
    {          
        
        $right = $this->getLayout()->getBlock('right');
        
        if ($right && Mage::helper('gomage_navigation')->isGomageNavigation() &&
            Mage::getStoreConfig('gomage_navigation/rightcolumnsettings/active'))
        {   
            $right->unsetChild('gomage.navigation.right');                  
            $page = Mage::getSingleton('cms/page');                                     
            if ($page->getData('page_id'))
            {                                                                       
                if ($page->getData('navigation_right_column'))
                {
                   $navigation_right = $this->getLayout()->createBlock('gomage_navigation/navigation', 'gomage.navigation.right')
                                                     ->setTemplate('gomage/navigation/catalog/navigation/right.phtml');
                   $navigation_right->SetNavigationPlace(GoMage_Navigation_Block_Navigation::RIGTH_COLUMN);  
                   $right->insert($navigation_right, '', false);
                }   
            }
            else
            {
                if (!Mage::getStoreConfig('gomage_navigation/rightcolumnsettings/show_shopby')){
                    $navigation_right = $this->getLayout()->createBlock('gomage_navigation/navigation', 'gomage.navigation.right')
                                                     ->setTemplate('gomage/navigation/catalog/navigation/right.phtml');
                    $navigation_right->SetNavigationPlace(GoMage_Navigation_Block_Navigation::RIGTH_COLUMN);  
                    $right->insert($navigation_right, '', false);                
                }
            }
        }                               

        parent::_prepareLayout();
        
    }    
}
