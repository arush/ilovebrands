<?php
class Evogue_CustomerSegment_Adminhtml_Report_Customer_CustomersegmentController extends Mage_Adminhtml_Controller_Action {

    protected $_adminSession = null;

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('report/customers')
            ->_addBreadcrumb(Mage::helper('evogue_customersegment')->__('Reports'),
                Mage::helper('evogue_customersegment')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('evogue_customersegment')->__('Customers'),
                Mage::helper('evogue_customersegment')->__('Customers'));
        return $this;
    }

    protected function _initSegment($outputMessage = true) {
        $segmentId = $this->getRequest()->getParam('segment_id', 0);
        $segmentIds = $this->getRequest()->getParam('massaction');
        if ($segmentIds) {
            $this->_getAdminSession()
                ->setMassactionIds($segmentIds)
                ->setViewMode($this->getRequest()->getParam('view_mode'));
        }

        $segment = Mage::getModel('evogue_customersegment/segment');

        if ($segmentId) {
            $segment->load($segmentId);
        }
        if ($this->_getAdminSession()->getMassactionIds()) {
            $segment->setMassactionIds($this->_getAdminSession()->getMassactionIds());
            $segment->setViewMode($this->_getAdminSession()->getViewMode());
        }
        if (!$segment->getId() && !$segment->getMassactionIds()) {
            if ($outputMessage) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong customer segment requested.'));
            }
            return false;
        }
        Mage::register('current_customer_segment', $segment);

        $websiteIds = $this->getRequest()->getParam('website_ids');
        if (!is_null($websiteIds) && empty($websiteIds)) {
            $websiteIds = null;
        } elseif (!is_null($websiteIds) && !empty($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        Mage::register('filter_website_ids', $websiteIds);

        return $segment;
    }

    public function indexAction() {
        $this->_forward('segment');
    }

    public function segmentAction() {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customer Segments'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                'evogue_customersegment/adminhtml_report_customer_segment')
            )
            ->renderlayout();
    }

    public function detailAction() {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customer Segments'));

        if ($this->_initSegment()) {

            // Add help Notice to Combined Report
            if ($this->_getAdminSession()->getMassactionIds()) {
                $collection = Mage::getResourceModel('evogue_customersegment/segment_collection')
                    ->addFieldToFilter('segment_id', array('in' => $this->_getAdminSession()->getMassactionIds()));

                $segments = array();
                foreach ($collection as $item) {
                    $segments[] = $item->getName();
                }
                /* @translation $this->__('Viewing combined "%s" report from segments: %s') */
                if ($segments) {
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('Viewing combined "%s" report from segments: %s.',
                            Mage::helper('evogue_customersegment')->getViewModeLabel($this->_getAdminSession()->getViewMode()),
                            implode(', ',$segments)
                        )
                    );
                }
            }

            $this->_title($this->__('Details'));

            $this->_initAction()->renderLayout();
        } else {
            $this->_redirect('*/*/segment');
            return ;
        }
    }

    public function refreshAction() {
        $segment = $this->_initSegment();
        if ($segment) {
            try {
                $segment->matchCustomers();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Customer Segment data has been refreshed.')
                );
                $this->_redirect('*/*/detail', array('_current' => true));
                return ;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/detail', array('_current' => true));
        return ;
    }

    public function exportExcelAction() {
        if ($this->_initSegment()) {
            $fileName = 'customersegment_customers.xml';
            $content = $this->getLayout()
                ->createBlock('evogue_customersegment/adminhtml_report_customer_segment_detail_grid')
                ->getExcelFile($fileName);
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', array('_current' => true));
            return ;
        }
    }

    public function exportCsvAction() {
        if ($this->_initSegment()) {
            $fileName = 'customersegment_customers.csv';
            $content = $this->getLayout()
                ->createBlock('evogue_customersegment/adminhtml_report_customer_segment_detail_grid')
                ->getCsvFile();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('*/*/detail', array('_current' => true));
            return ;
        }
    }

    public function customerGridAction() {
        if (!$this->_initSegment(false)) {
            return;
        }
        $grid = $this->getLayout()->createBlock('evogue_customersegment/adminhtml_report_customer_segment_detail_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    protected function _getAdminSession() {
        if (is_null($this->_adminSession)) {
            $this->_adminSession = Mage::getModel('admin/session');
        }
        return $this->_adminSession;
    }
}
