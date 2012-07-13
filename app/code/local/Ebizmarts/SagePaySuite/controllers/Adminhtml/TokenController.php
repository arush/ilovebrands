<?php

/**
 * Admin TOKEN grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Adminhtml_TokenController extends Mage_Adminhtml_Controller_Action
{
	protected $_eoln = Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_DELIM_CHAR;

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Sage Pay Cards'), $this->__('Sage Pay Cards'));
        return $this;
    }

	public function indexAction()
	{
        $this->_title($this->__('Sales'))->_title($this->__('Sage Pay Cards'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_tokencard'))
            ->renderLayout();
	}

	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_tokencard_grid')->toHtml()
        );
	}

	public function newAction()
	{
		$this->loadLayout();

		Mage::register('admin_tokenregister', $this->getRequest()->getParam('customer_id'));

		$result = Mage::getModel('sagepaysuite/sagePayToken')->registerCard();
		if(!isset($result['NextURL'])){
			$this->_getSession()->addError($this->__('Could not register token, please try again.'));
			$this->_redirectReferer();
			return;
		}

		$this->getResponse()->setBody(
            '<iframe style="width:100%;height:100%;padding:0;margin:0;border:none;" src="'.$result['NextURL'].'"></iframe>'
        );
	}

	public function registerPostAction()
	{
	  $post = $this->getRequest()->getPost();

	  $response = '';

	   if($post['Status'] == 'OK'){

		 $post['protocol'] = 'server';

	     $_save = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')
	     ->setToken($post['Token'])
	     ->setStatus($post['Status'])
	     ->setCardType($post['CardType'])
	     ->setExpiryDate($post['ExpiryDate'])
	     ->setStatusDetail($post['StatusDetail'])
	     ->setProtocol($post['protocol'])
	     ->setCustomerId($this->getRequest()->getParam('cid'))
	     ->setLastFour($post['Last4Digits'])
	     ->save();

	     $response .= 'Status=OK' . $this->_eoln;
	     $response .= 'RedirectURL=' . Mage::helper('adminhtml')->getUrl('sgpsSecure/adminhtml_token/registerSuccess') . $this->_eoln;
         $response .= 'StatusDetail=Card successfully registered.' . $this->_eoln;

	   }else if($post['Status'] == 'ABORT'){

			$response .= 'Status=OK' . $this->_eoln;
	     	$response .= 'RedirectURL=' . Mage::helper('adminhtml')->getUrl('sgpsSecure/adminhtml_token/registerAbort') . $this->_eoln;
         	$response .= 'StatusDetail=Card registering was aborted. ' . $post['StatusDetail'] . $this->_eoln;

	   }


		$this->getResponse()->setHeader('Content-type', 'text/plain');
		return $this->getResponse()->setBody($response);
	}

	public function registerSuccessAction()
	{
          return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <script type="text/javascript">
																window.parent.parent.location.reload();
														</script>
                                                    </body>
                                                </html>');
	}

	public function registerAbortAction()
	{
          return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <script type="text/javascript">
																window.parent.parent.location.reload();
														</script>
                                                    </body>
                                                </html>');
	}

	public function deleteCardAction()
	{
		$card = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($this->getRequest()->getParam('id'));

		if($card->getId()){
			$result = Mage::getModel('sagepaysuite/sagePayToken')->removeCard($card->getToken());

			if($result['Status'] == 'OK'){
			  //Delete card on our Database
			  $card->delete();
			  $this->_getSession()->addSuccess($this->__('Card deleted successfully'));
			}else{
			  $this->_getSession()->addError($this->__($result['StatusDetail']));
			}

			$this->_redirectReferer();
			return;
		}

		$this->_getSession()->addError($this->__('This card does not exist.'));
		$this->_redirectReferer();
		return;
	}
}