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

class GoMage_Navigation_Block_Navigation_Left extends Mage_Core_Block_Template
{
        
    protected function _prepareLayout()
    {          
        
        $left = $this->getLayout()->getBlock('left');        
                                
        if ($left && Mage::helper('gomage_navigation')->isGomageNavigation() &&
            Mage::getStoreConfig('gomage_navigation/category/active'))
        {   
            $left->unsetChild('gomage.navigation.left');                  
            $page = Mage::getSingleton('cms/page');                                     
            if ($page->getData('page_id'))
            {                                                                       
                if ($page->getData('navigation_left_column'))
                {
                   $navigation_left = $this->getLayout()->createBlock('gomage_navigation/navigation', 'gomage.navigation.left')
                                                     ->setTemplate('gomage/navigation/catalog/navigation/left.phtml');
                   $navigation_left->SetNavigationPlace(GoMage_Navigation_Block_Navigation::LEFT_COLUMN);  
                   $left->insert($navigation_left);
                }   
            }
            else
            {                                                                                                   
                if (!Mage::getStoreConfig('gomage_navigation/category/show_shopby'))
                {
                    $navigation_left = $this->getLayout()->createBlock('gomage_navigation/navigation', 'gomage.navigation.left')
                                                     ->setTemplate('gomage/navigation/catalog/navigation/left.phtml');
                    $navigation_left->SetNavigationPlace(GoMage_Navigation_Block_Navigation::LEFT_COLUMN);                
                    $left->insert($navigation_left);
                }
            }
        }
                                                 
        parent::_prepareLayout();
        
    }    
}
