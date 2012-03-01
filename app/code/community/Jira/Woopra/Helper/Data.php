<?php
/**
 * Woopra plugin for Magento 
 *
 * @category    design_default
 * @package     Jira_Woopra
 * @author      Yireo
 * @copyright   Copyright (c) 2009 Yireo (http://www.yireo.com/)
 * @license     Open Software License
 */

class Jira_Woopra_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getApiKey()
    {
        return Mage::getStoreConfig('woopra/settings/website_id');
    }

    public function enabled()
    {
        return (bool)Mage::getStoreConfig('woopra/settings/active');
    }

    public function test()
    {
        return (bool)Mage::getStoreConfig('woopra/settings/test');
    }
}
