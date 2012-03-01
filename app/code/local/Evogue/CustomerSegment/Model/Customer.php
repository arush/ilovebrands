<?php
class Evogue_CustomerSegment_Model_Customer extends Mage_Core_Model_Abstract {

    protected $_segmentMap = array();

    protected $_customerSegments = array();

    protected $_customerWebsiteSegments = array();

    protected function _construct() {
        parent::_construct();
        $this->_init('evogue_customersegment/customer');
    }

    public function getActiveSegmentsForEvent($eventName, $websiteId) {
        if (!isset($this->_segmentMap[$eventName][$websiteId])) {
            $this->_segmentMap[$eventName][$websiteId] = Mage::getResourceModel('evogue_customersegment/segment_collection')
                ->addEventFilter($eventName)
                ->addWebsiteFilter($websiteId)
                ->addIsActiveFilter(1);
        }
        return $this->_segmentMap[$eventName][$websiteId];
    }

    public function processEvent($eventName, $customer, $website) {
        Varien_Profiler::start('__SEGMENTS_MATCHING__');
        $website    = Mage::app()->getWebsite($website);
        $websiteId  = $website->getId();
        $segments = $this->getActiveSegmentsForEvent($eventName, $websiteId);
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customerId = $customer->getId();
        } else {
            $customerId = $customer;
        }
        $matchedIds     = array();
        $notMatchedIds  = array();
        foreach ($segments as $segment) {
            $isMatched = $segment->validateCustomer($customer, $website);
            if ($isMatched) {
                $matchedIds[]   = $segment->getId();
            } else {
                $notMatchedIds[]= $segment->getId();
            }
        }

        $this->addCustomerToWebsiteSegments($customerId, $websiteId, $matchedIds);
        $this->removeCustomerFromWebsiteSegments($customerId, $websiteId, $notMatchedIds);

        Varien_Profiler::stop('__SEGMENTS_MATCHING__');
        return $this;
    }

    public function processCustomer(Mage_Customer_Model_Customer $customer, $website) {
        $website = Mage::app()->getWebsite($website);
        $segments = Mage::getResourceModel('evogue_customersegment/segment_collection')
            ->addWebsiteFilter($website)
            ->addIsActiveFilter(1);

        $matchedIds     = array();
        $notMatchedIds  = array();
        foreach ($segments as $segment) {
            $isMatched = $segment->validateCustomer($customer, $website);
            if ($isMatched) {
                $matchedIds[]   = $segment->getId();
            } else {
                $notMatchedIds[]= $segment->getId();
            }
        }
        $this->addCustomerToWebsiteSegments($customer->getId(), $website->getId(), $matchedIds);
        $this->removeCustomerFromWebsiteSegments($customer->getId(), $website->getId(), $notMatchedIds);
        return $this;
    }

    public function processCustomerEvent($eventName, $customerId) {
        if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
            $websiteIds = Mage::getResourceSingleton('customer/customer')->getWebsiteId($customerId);
            if ($websiteIds) {
                $websiteIds = array($websiteIds);
            } else {
                $websiteIds = array();
            }
        } else {
            $websiteIds = Mage::app()->getWebsites();
            $websiteIds = array_keys($websiteIds);
        }
        foreach ($websiteIds as $websiteId) {
            $this->processEvent($eventName, $customerId, $websiteId);
        }
        return $this;
    }

    public function addCustomerToWebsiteSegments($customerId, $websiteId, $segmentIds) {
        $existingIds = $this->getCustomerSegmentIdsForWebsite($customerId, $websiteId);
        $this->_getResource()->addCustomerToWebsiteSegments($customerId, $websiteId, $segmentIds);
        $this->_customerWebsiteSegments[$websiteId][$customerId] = array_unique(array_merge($existingIds, $segmentIds));
        return $this;
    }

    public function removeCustomerFromWebsiteSegments($customerId, $websiteId, $segmentIds) {
        $existingIds = $this->getCustomerSegmentIdsForWebsite($customerId, $websiteId);
        $this->_getResource()->removeCustomerFromWebsiteSegments($customerId, $websiteId, $segmentIds);
        $this->_customerWebsiteSegments[$websiteId][$customerId] = array_diff($existingIds, $segmentIds);
        return $this;
    }

    public function getCustomerSegmentIdsForWebsite($customerId, $websiteId) {
        if (!isset($this->_customerWebsiteSegments[$websiteId][$customerId])) {
            $this->_customerWebsiteSegments[$websiteId][$customerId] = $this->_getResource()
                ->getCustomerWebsiteSegments($customerId, $websiteId);
        }
        return $this->_customerWebsiteSegments[$websiteId][$customerId];
    }

    public function addCustomerToSegments($customer, $segmentIds) {
        $customerId = $customer->getId();
        $existingIds = $this->getCustomerSegmentIds($customer);
        $this->_getResource()->addCustomerToSegments($customerId, $segmentIds);
        $this->_customerSegments[$customerId] = array_unique(array_merge($existingIds, $segmentIds));
        return $this;
    }

    public function removeCustomerFromSegments($customer, $segmentIds) {
        $customerId = $customer->getId();
        $existingIds = $this->getCustomerSegmentIds($customer);
        $this->_getResource()->removeCustomerFromSegments($customerId, $segmentIds);
        $this->_customerSegments[$customerId] = array_diff($existingIds, $segmentIds);
        return $this;
    }

    public function getCustomerSegmentIds(Mage_Customer_Model_Customer $customer) {
        $customerId = $customer->getId();
        if (!isset($this->_customerSegments[$customerId])) {
            $this->_customerSegments[$customerId] = $this->_getResource()->getCustomerSegments($customerId);
        }
        return $this->_customerSegments[$customerId];
    }
}
