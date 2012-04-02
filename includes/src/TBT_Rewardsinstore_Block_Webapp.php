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
 * The container page for the Instore webapp (contains the Login or Main block)
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Block_Webapp extends Mage_Core_Block_Template
{
    protected $_storefronts = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_controller = 'webapp';
        $this->_blockGroup = 'rewardsinstore';
        $this->_headerText = $this->__('Sweet Tooth Instore');
        $this->setTemplate("");
        $this->setUseAjax(true);
    }
    
    protected function getStorefrontCollection()
    {
        if (!$this->_storefronts) {
            $this->_storefronts = Mage::getModel('rewardsinstore/storefront')->getCollection();
        }
        
        return $this->_storefronts;
    }
    
    protected function getLaunchUri()
    {
        $key = Mage::getSingleton('core/session')->getCookie()->get('rewardsinstore-key');
        if ($key) {
            return Mage::getModel('adminhtml/url')->getUrl('rewardsinstoreadmin/webapp_ajax/main', array('key' => $key));
        }
        
        // Get all param keys, the first of which will be our storefront code
        $params = array_keys($this->getRequest()->getParams());
        
        // If there's a parameter, use its key as our storefront code.
        return Mage::getModel('adminhtml/url')->getUrl('rewardsinstore/index/login', count($params) ? array('code' => $params[0]) : array());
    }
    
    protected function isLoggedIn()
    {
        return Mage::getSingleton('core/session')->getCookie()->get('rewardsinstore-key');
    }
    
    protected function getStorefronts()
    {
        $storefronts = $this->getStorefrontCollection();
        
        $simplifiedStorefronts = array();
        foreach ($storefronts as $storefront) {
            $simplifiedStorefronts[] = (object) array(
                'id'      => $storefront->getId(),
                'code'    => $storefront->getCode(),
                'name'    => $storefront->getName(),
                'address' => $storefront->getFormattedAddress());
        }
        
        return Zend_Json::encode($simplifiedStorefronts);
    }
}
