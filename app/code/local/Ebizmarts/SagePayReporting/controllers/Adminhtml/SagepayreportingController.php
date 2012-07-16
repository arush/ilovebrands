<?php

class Ebizmarts_SagePayReporting_Adminhtml_SagePayReportingController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('sagepayreporting/transaction_detail')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('sagepayreporting/sagepayreporting')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('sagepayreporting_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('sagepayreporting/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_edit'))
			->_addLeft($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sagepayreporting')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {
					/* Starting upload */
					$uploader = new Varien_File_Uploader('filename');

					// Any extention would work
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);

					// Set the file upload mode
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);

					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );

				} catch (Exception $e) {

				}

				//this way the name is saved in DB
				$data['filename'] = $_FILES['filename']['name'];
			}


			$model = Mage::getModel('sagepayreporting/sagepayreporting');
			$model->setData($data)
			->setId($this->getRequest()->getParam('id'));

			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
					->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sagepayreporting')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sagepayreporting')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('sagepayreporting/sagepayreporting');

				$model->setId($this->getRequest()->getParam('id'))
				->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		$sagepayreportingIds = $this->getRequest()->getParam('sagepayreporting');
		if(!is_array($sagepayreportingIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($sagepayreportingIds as $sagepayreportingId) {
					$sagepayreporting = Mage::getModel('sagepayreporting/sagepayreporting')->load($sagepayreportingId);
					$sagepayreporting->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($sagepayreportingIds)
				)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction()
	{
		$sagepayreportingIds = $this->getRequest()->getParam('sagepayreporting');
		if(!is_array($sagepayreportingIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		} else {
			try {
				foreach ($sagepayreportingIds as $sagepayreportingId) {
					$sagepayreporting = Mage::getSingleton('sagepayreporting/sagepayreporting')
					->load($sagepayreportingId)
					->setStatus($this->getRequest()->getParam('status'))
					->setIsMassupdate(true)
					->save();
				}
				$this->_getSession()->addSuccess(
				$this->__('Total of %d record(s) were successfully updated', count($sagepayreportingIds))
				);
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function exportCsvAction()
	{
		$fileName   = 'sagepayreporting.csv';
		$content    = $this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_grid')
		->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction()
	{
		$fileName   = 'sagepayreporting.xml';
		$content    = $this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_grid')
		->getXml();

		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK','');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}

	public function transactionDetailAction()
	{
		if ($this->getRequest()->getParam('vendortxcode')) {
			$id = $this->getRequest()->getParam('vendortxcode');
			$response = Mage::getModel('sagepayreporting/sagepayreporting')->getTransactionDetails(urldecode($id), null);
			if ($response->getErrorcode() == '0000') {
				Mage::register('sagepay_detail', $response);
			} else {
				$this->_getSession()->addError($response->getError());
			}
		} else {
			Mage::register('sagepay_detail', null);
		}

		$this->loadLayout()
		->_setActiveMenu('sales')
		->_addContent($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_transaction_detail'))
		->renderLayout();
	}

	public function transactionDetailModalAction()
	{
		if ($this->getRequest()->getParam('vendortxcode') || $this->getRequest()->getParam('vpstxid')) {
			$id = $this->getRequest()->getParam('vendortxcode') ? $this->getRequest()->getParam('vendortxcode') : $this->getRequest()->getParam('vpstxid');

			if($this->getRequest()->getParam('vpstxid')){
				$response = Mage::getModel('sagepayreporting/sagepayreporting')->getTransactionDetails(null, urldecode($id));
				Mage::register('sagepay_related_transactions', Mage::getModel('sagepayreporting/sagepayreporting')->getRelatedTransactions(urldecode($id)));
			}else{
				Mage::register('sagepay_related_transactions', new Varien_Object);
				$response = Mage::getModel('sagepayreporting/sagepayreporting')->getTransactionDetails($id, null);
			}

			if ($response->getErrorcode() == '0000') {
				Mage::register('sagepay_detail', $response);
			} else {
				$this->_getSession()->addError($response->getError());
			}
		} else {
			Mage::register('sagepay_detail', null);
		}

		$this->loadLayout('popup_sagepay')
		->renderLayout();
	}

	public function threedstatusAction()
	{
		$this->loadLayout()
		->_setActiveMenu('sales')
		->_addContent($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_threedstatus'))
		->renderLayout();
	}

	public function avscvstatusAction()
	{
		$this->loadLayout()
		->_setActiveMenu('sales')
		->_addContent($this->getLayout()->createBlock('sagepayreporting/adminhtml_sagepayreporting_avscvstatus'))
		->renderLayout();
	}

	public function avscvtoggleAction()
	{
		$currentStatus = $this->getRequest()->getParam('st');

		if($currentStatus){
			$newStatus = ($currentStatus == 'ON' ? 'OFF' : 'ON');
			try{
				$result = Mage::getModel('sagepayreporting/sagepayreporting')->setAVSCV2Status($newStatus);
				$this->_getSession()->addSuccess(Mage::helper('sagepayreporting')->__('Status changed successfully'));
			}catch(Exception $e){
				$this->_getSession()->addError($e->getMessage());
			}
		}

		$this->_redirectReferer();
		return;
	}

	public function threedtoggleAction()
	{
		$currentStatus = $this->getRequest()->getParam('st');

		if($currentStatus){
			$newStatus = ($currentStatus == 'ON' ? 'OFF' : 'ON');
			try{
				$result = Mage::getModel('sagepayreporting/sagepayreporting')->set3dSecureStatus($newStatus);
				$this->_getSession()->addSuccess(Mage::helper('sagepayreporting')->__('Status changed successfully'));
			}catch(Exception $e){
				$this->_getSession()->addError($e->getMessage());
			}
		}

		$this->_redirectReferer();
		return;
	}

	public function showpostAction()
	{
		$postVendor = Mage::getModel('sagepaysuite/api_payment')->showPost();

		$this->_getSession()->addSuccess($this->__('Vendorname used: %s', $postVendor));

		$this->_redirectReferer();
		return;
	}

}