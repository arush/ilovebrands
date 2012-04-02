<?php

/**
 * CARD frontend controller, token
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_CardController extends Mage_Core_Controller_Front_Action
{

	protected $_eoln = Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_DELIM_CHAR;

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

	protected function _getServerRegisterCard()
	{
		return Mage::getModel('sagepaysuite/sagePayToken')->registerCard();
	}

	public function serverformAction()
	{
		$rs = $this->_getServerRegisterCard();

		if(!isset($rs['NextURL'])){
			echo '<html><body>';
			echo $rs['StatusDetail'];
			echo '<script type="text/javascript">setTimeout(function(){window.parent.Control.Window.windows.each(function(w){
																						if(w.container.visible()){
																							w.close();
																						}
																	   }); try{window.parent.checkout.accordion.openSection("opc-payment")}catch(a){}}, 3000)</script></html></body>';
			return;
		}

		echo '<style type="text/css">iframe#iframeRegCard {height:518px; width:100%; border:none;}</style>';
		echo $this->getLayout()->createBlock('sagepaysuite/customer_account_card_server_form')->setNextUrl($rs['NextURL'])->toHtml();

		return;
	}

	public function registerAction()
	{

        /*
         * DIRECT POST
         */
	  if($this->getRequest()->isPost()){ #DIRECT POST

		$post = $this->getRequest()->getPost();

		$post['ExpiryDate'] = str_pad($post['ExpiryMonth'], 2, '0', STR_PAD_LEFT) . substr($post['ExpiryYear'], 2);

        if($post['StartMonth'] && $post['StartYear']){
            $post['StartDate'] = str_pad($post['StartMonth'], 2, '0', STR_PAD_LEFT) . substr($post['StartYear'], 2);
        }

	    $rs = Mage::getModel('sagepaysuite/sagePayToken')->registerCard($post);

		if(empty($rs)){
		  $rs['Status'] = 'ERROR';
		  $rs['StatusDetail'] = 'A server to server communication error ocured, please try again later.';
		}

	    if($rs['Status'] == 'OK'){
		 $save = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')
	     ->setToken($rs['Token'])
	     ->setStatus($rs['Status'])
	     ->setCardType($post['CardType'])
	     ->setExpiryDate($post['ExpiryDate'])
	     ->setStatusDetail($rs['StatusDetail'])
	     ->setProtocol('direct')
	     ->setCustomerId($this->_getCustomerId())
	     ->setLastFour(substr($post['CardNumber'], -4))
	     ->save();

            $rs = array_change_key_case($rs);
            $resp = $rs;
            $resp ['mark'] = array(
                                    'cctype' => $save->getLabel(),
                                    'id' => $save->getId(),
                                    'defaultchecked' => ($save->getIsDefault() == 1 ? ' checked="checked"' : ''),
                                    'ccnumber' => $save->getCcNumber(),
                                    'exp' => $save->getExpireDate(),
                                    'delurl' => Mage::getUrl('sgps/card/delete', array('card'=>$save->getId()))
                                  );
        }else{
            $rs = array_change_key_case($rs);
            $resp = $rs;
        }

	    return $this->getResponse()->setBody(Zend_Json::encode($resp));

	  }
        /*
         * DIRECT POST
         */

	  $url = '';

	  if(false === Mage::getModel('sagepaysuite/sagePayToken')->customerCanAddCard()){

		$url = 'ERROR';
		$text = $this->__('You can\'t add more cards, please delete one if you want to add another.');

	  }else{

		  $int = $this->_getTokenIntegrationType();

		  if($int == 'server'){

			$rs = $this->_getServerRegisterCard();

			if($rs['Status'] == 'OK'){
				$text = $this->getLayout()->createBlock('sagepaysuite/customer_account_card_server_form')->setNextUrl($rs['NextURL'])->toHtml();
			}else{
		  		$url = 'ERROR';
		    	$text = $rs['StatusDetail'];
		  	}

		  }else{
		    $text = $this->getLayout()->createBlock('sagepaysuite/customer_account_card_direct_form')->toHtml();
		  }

	  }


	  return $this->getResponse()->setBody(Zend_Json::encode(array('text'=>$text, 'url'=>$url)));
	}

	public function closeserverformAction()
	{
	return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <script type="text/javascript">
															if ( window.parent.$(\'multishipping-billing-form\') ){
																window.parent.$(\'multishipping-billing-form\').submit();
															}
															window.parent.Control.Window.windows.each(function(w){
																						if(w.container.visible()){
																							w.close();
																						}
																	   });
														</script>
                                                    </body>
                                                </html>');
	}

	public function closeserverabortformAction()
	{
	return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <script type="text/javascript">
															window.parent.Control.Window.windows.each(function(w){
																						if(w.container.visible()){
																							w.close();
																						}
																	   });
															window.parent.checkout.accordion.openSection("opc-payment");
														</script>
                                                    </body>
                                                </html>');
	}

	public function registerSuccessAction()
	{
          return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <!--<h2>'.$this->__('Redirecting').'</h2>-->
														<div style="background-image:url(' . Mage::helper('sagepaysuite')->getIndicator() . '); background-position: center center;background-repeat: no-repeat;height: 400px;">&nbsp;</div>
                                                        <script type="text/javascript">
															var url = window.parent.location.href;
															if(url.match(/serverform/gi)){
																window.parent.location.href="'.Mage::getUrl('sgps/card/closeserverform').'";
															}else{
																window.parent.location.reload();
															}
														</script>
                                                    </body>
                                                </html>');
	}

	public function registerAbortAction()
	{
          return $this->getResponse()->setBody('<html>
                                                    <body>
                                                        <!--<h2>'.$this->__('Redirecting').'</h2>-->
														<div style="background-image:url(' . Mage::helper('sagepaysuite')->getIndicator() . '); background-position: center center;background-repeat: no-repeat;height: 400px;">&nbsp;</div>
                                                        <script type="text/javascript">
															var url = window.parent.location.href;
															if(url.match(/serverform/gi)){
																window.parent.location.href="'.Mage::getUrl('sgps/card/closeserverabortform').'";
															}else{
																window.parent.location.reload();
															}
														</script>
                                                    </body>
                                                </html>');
	}

	public function registerPostAction()
	{
	  $post = $this->getRequest()->getPost();

	  $response = '';

	   if($post['Status'] == 'OK'){

		 $post['protocol'] = 'direct';
	   	 if(array_key_exists('ExpiryDate', $post)){
			$post['protocol'] = 'server';
	   	 }

		 $sessId = Mage::getModel('sagepaysuite/api_payment')->getCustomerQuoteId();

	     $_save = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard');

         if(is_string($sessId)){
             $_save->setVisitorSessionId($sessId);
         }

	     $_save->setToken($post['Token'])
	     ->setStatus($post['Status'])
	     ->setCardType($post['CardType'])
	     ->setExpiryDate($post['ExpiryDate'])
	     ->setStatusDetail($post['StatusDetail'])
	     ->setProtocol($post['protocol'])
	     ->setCustomerId($this->_getCustomerId())
	     ->setLastFour($post['Last4Digits'])
	     ->save();

		 Mage::getSingleton('sagepaysuite/session')->setLastSavedTokenccid($_save->getId());

	     $response .= 'Status=OK' . $this->_eoln;
	     $response .= 'RedirectURL=' . Mage::getUrl('sgps/card/registerSuccess') . '?SID=' . $this->getRequest()->getParam('SID', '') . $this->_eoln;
         $response .= 'StatusDetail=Card successfully registered.' . $this->_eoln;
	   }else if($post['Status'] == 'ABORT'){

			$response .= 'Status=OK' . $this->_eoln;
	     	$response .= 'RedirectURL=' . Mage::getUrl('sgps/card/registerAbort') . '?SID=' . $this->getRequest()->getParam('SID', '') . $this->_eoln;
         	$response .= 'StatusDetail=Card registering was aborted. ' . $post['StatusDetail'] . $this->_eoln;

	   }


		$this->getResponse()->setHeader('Content-type', 'text/plain');
		return $this->getResponse()->setBody($response);
	}

	public function indexAction()
	{
        if( !$this->_getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('sgps/card');
        }
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sagepaysuite')->__('Sage Pay - Saved Credit Cards'));
        $this->renderLayout();
	}

	public function deleteAction()
	{
		$resp = array('st'=>'nok', 'text'=>'');

        if(!Mage::getSingleton('customer/session')->authenticate($this)) {
        	$resp ['text'] = $this->__('Please login, you session expired.');
        	$this->getResponse()->setBody(Zend_Json::encode($resp));
            return;
        }

        $cardId  = (int)$this->getRequest()->getParam('card');

		$objCard = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($cardId);
		if($objCard->getId()){

			# Check if card is from this customer
			if($objCard->getCustomerId() != $this->_getCustomerId()){
	        	$resp ['text'] = $this->__('Invalid Card #');
	        	$this->getResponse()->setBody(Zend_Json::encode($resp));
	            return;
			}

			try{
				$delete = Mage::getModel('sagepaysuite/sagePayToken')->removeCard($objCard->getToken(), $objCard->getProtocol());

				if($delete['Status'] == 'OK'){
					$objCard->delete();

		        	$resp ['text'] = $this->__('Success!');
		        	$resp ['st'] = 'ok';
		        	$this->getResponse()->setBody(Zend_Json::encode($resp));
		            return;
				}else{
		        	$resp ['text'] = $this->__('An error ocured, %s', $delete['StatusDetail']);
		        	$this->getResponse()->setBody(Zend_Json::encode($resp));
		            return;
				}

			}catch(Exception $e){

	        	$resp ['text'] = $this->__($e->getMessage());
	        	$this->getResponse()->setBody(Zend_Json::encode($resp));
	            return;

			}

		}

      	$resp ['text'] = $this->__('The requested Card does not exist.');
      	$this->getResponse()->setBody(Zend_Json::encode($resp));
        return;

	}

	/*
	 * Set default card
	 */
	public function defaultAction()
	{
		$resp = array('st'=>'nok', 'text'=>'');

        if(!Mage::getSingleton('customer/session')->authenticate($this)) {
        	$resp ['text'] = $this->__('Please login, you session expired.');
        	$this->getResponse()->setBody(Zend_Json::encode($resp));
            return;
        }

        $cardId  = (int)$this->getRequest()->getParam('card');

		$objCard = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($cardId);
		if($objCard->getId()){

			# Check if card is from this customer
			if($objCard->getCustomerId() != $this->_getCustomerId()){
	        	$resp ['text'] = $this->__('Invalid Card #');
	        	$this->getResponse()->setBody(Zend_Json::encode($resp));
	            return;
			}

			try{

				$objCard->setIsDefault(1)
				->save();

	        	$resp ['text'] = $this->__('Success!');
	        	$resp ['st'] = 'ok';
	        	$this->getResponse()->setBody(Zend_Json::encode($resp));
	            return;

			}catch(Exception $e){

	        	$resp ['text'] = $this->__($e->getMessage());
	        	$this->getResponse()->setBody(Zend_Json::encode($resp));
	            return;

			}

		}

      	$resp ['text'] = $this->__('The requested Card does not exist.');
      	$this->getResponse()->setBody(Zend_Json::encode($resp));
        return;
	}

	protected function _getCustomerId()
	{
	  return (int)Mage::getModel('sagepaysuite/sagePayToken')->getSessionCustomerId();
	}

	protected function _getTokenIntegrationType()
	{
		return (string)Mage::getStoreConfig('payment/sagepaysuite/token_integration');
	}
}
