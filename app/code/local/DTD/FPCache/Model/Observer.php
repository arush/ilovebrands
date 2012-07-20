<?php


/**
 * Main event observer model
 *
 */

class DTD_FPCache_Model_Observer
{

	public function processPreDispatch(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();

        // Check to see if $action is a Product controller
        if ($action instanceof Mage_Catalog_ProductController
        || $action instanceof Mage_Catalog_CategoryController)
        {
            $request = $action->getRequest();
            $cache = Mage::app()->getCacheInstance();

            // Tell Magento to 'ban' the use of FPC for this request
            $cache->banUse('full_page');
        }

    }

}
