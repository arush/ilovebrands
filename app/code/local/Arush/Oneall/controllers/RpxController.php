<?php

require_once("Mage/Customer/controllers/AccountController.php");

class Arush_Oneall_RpxController extends Mage_Customer_AccountController {

	/**
     * Action predispatch
     *
     * Check customer authentication for some actions
	 *
	 * This is a clone of the one in Mage_Customer_AccountController
	 * with two added action names to the preg_match regex to prevent
	 * redirects back to customer/account/login when using Oneall
	 * authentication links. Rather than calling parent::preDispatch()
	 * we explicitly call Mage_Core_Controller_Front_Action to prevent the
	 * original preg_match test from breaking our auth process.
	 * 
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!preg_match('/^(addIdentifier|token_url_add|token_url|authenticate|duplicate|create|login|logoutSuccess|forgotpassword|forgotpasswordpost|confirm|confirmation)/i', $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

	public function indexAction() {
		$this->_redirect('customer/account/index');
	}

	/**
	 * Oneall Login Callback
	 */
	public function token_urlAction() {
		$session = $this->_getSession();

		// Redirect if user is already authenticated
		if ($session->isLoggedIn()) {
			$this->_redirect('customer/account');
			return;
		}

		if ($this->getRequest()->isPost()) {
			$token = $this->getRequest()->getPost('oa_social_login_token');

			if($token){
				// Store token in session under random key
				$key = Mage::helper('oneall')->rand_str(12);
				Mage::getSingleton('oneall/session')->setData($key, $token);

				// Redirect user to $this->authAction method passing $key as ses
				// $_GET variable (Magento style)
				$this->_redirect("arush-oneall/rpx/authenticate", array("ses" => $key));
				return;
			} else {
				$session->addError('Authentication token not received. Please try again.');
			}
		}

		$this->_redirect('customer/account/login');
	}

	/**
	 * Oneall Callback for Social Link
	 */
	public function token_url_addAction(){
		$session = $this->_getSession();
		
		// Redirect if user isn't already authenticated
		if (!$session->isLoggedIn()) {
			$this->_redirect('customer/account/login');
			return;
		}

		if ($this->getRequest()->getPost('connection_token')) {
			
			$token = $this->getRequest()->getPost('connection_token');
			//testing echo
			//echo $token;
			
			// Store token in session under random key
			$key = Mage::helper('oneall')->rand_str(12);
			Mage::getSingleton('oneall/session')->setData($key, $token);

			
			// Redirect user to $this->authAction method passing $key as ses
			$this->_redirect("arush-oneall/rpx/checkLink", array("ses" => $key));
		}
		//else { echo 'watt up';}
	}

	
	public function authenticateAction() {
		$session = $this->_getSession();

		$key = $this->getRequest()->getParam('ses');
		$token = Mage::getSingleton('oneall/session')->getData($key);
		$auth_info = Mage::helper('oneall/rpxcall')->rpxAuthInfoCall($token);
		

		if(isset($auth_info) && $auth_info->response->result->status->code ===200) {
			
			
			//$customer = Mage::helper('oneall/identifiers')->get_customer(Mage::helper('oneall')->getSocialId($auth_info));
			$customerCollection = Mage::getModel('customer/customer')
			->getCollection()
			->addAttributeToSelect('oneall_user_token')
			->addAttributeToFilter('oneall_user_token', $auth_info->response->result->data->user->user_token);

			if ($customerCollection->count() == 0) {
				$this->loadLayout();
				$block = Mage::getSingleton('core/layout')->getBlock('customer_form_register');
				if($block !== false) {
					$form_data = $block->getFormData();

					if(isset($auth_info->response->result->data->user->identity->emails[0]->value)) {
						$email = $auth_info->response->result->data->user->identity->emails[0]->value; }
					/*else if(isset($auth_info->profile) && isset($auth_info->profile->email))
						$email = $auth_info->profile->email; */
					else {
						$email = '';
					}

					$firstName = Mage::helper('oneall/rpxcall')->getFirstName($auth_info);
					$lastName = Mage::helper('oneall/rpxcall')->getLastName($auth_info);
					$gender = Mage::helper('oneall/rpxcall')->getGender($auth_info);
					
					$form_data->setEmail($email);
					$form_data->setFirstname($firstName);
					$form_data->setLastname($lastName);
					$form_data->setGender($gender);
				}
				$profile = Mage::helper('oneall')->buildProfile($auth_info);
				Mage::getSingleton('oneall/session')->setIdentifier($profile);

				$this->renderLayout();
				return;
			} else {
				$customer = $customerCollection->getFirstItem();
				Mage::getSingleton('oneall/session')->setLoginRequest(true);
				//$session->login($customer->getEmail(), 'REQUIRED_SECOND_PARAM');
				$session->setCustomerAsLoggedIn($customer);
				//$this->_redirectReferer();
				//Mage::dispatchEvent('customer_login', array('customer'=>$customer));
				$this->_loginPostRedirect();
			}
		} else {
			$session->addError('Could not retrieve account info. Please try again.');
			$this->_redirect('customer/account/login');
		
		}
	}
	
	
	public function checkLinkAction() {
	
		$session = $this->_getSession();

		$key = $this->getRequest()->getParam('ses');
		$token = Mage::getSingleton('oneall/session')->getData($key);
		//$uuid = Mage::helper('oneall')->getUuid();
		
		//retrieve info from oneall about this connection
		$linkResponse = Mage::helper('oneall/rpxcall')->rpxAuthInfoCall($token);
		
		$deleteTrue = Mage::helper('oneall')->getDeleteTrue($linkResponse);
		if($deleteTrue == true) {
			$deleteToken = $linkResponse->response->result->data->user->identity->identity_token;
			
			// Store deleteToken in session under 'delete'
			Mage::getSingleton('oneall/session')->setData('delete', $deleteToken);

			
			// Redirect user to $this->removeIdAction
			//echo 'removing';

			$this->_redirect("arush-oneall/rpx/removeId", array("delete" => $deleteToken));

		}
		else if($deleteTrue == false) {
			
			Mage::getSingleton('oneall/session')->setData('response', $linkResponse);
			
			
			$this->_redirect("arush-oneall/rpx/addIdentifier", array("ses" => 'response'));
		}
		
		
	}
	
	public function addIdentifierAction() {
		
		$session = $this->_getSession();
		
		$key = $this->getRequest()->getParam('ses');
		$response = Mage::getSingleton('oneall/session')->getData($key);

		$user_token = Mage::helper('oneall')->getUserToken($response);		
		
		$customer = Mage::helper('oneall/identifiers')->get_customer_from_user_token($user_token);
		
				
		if ($customer===false) {
			$customer_id = $session->getCustomer()->getId();
			$profile = Mage::helper('oneall')->buildProfile($response);

			Mage::helper('oneall/identifiers')
					->save_identifier($customer_id, $profile);

			$session->addSuccess('New identity successfully added.');

		} else {
			$session->addError('Could not add identity. This account is already linked to another user.');
		}

		$this->_redirect('customer/account');
	}

	public function createPostAction() {
		$session = $this->_getSession();
		parent::createPostAction();

		$messages = $session->getMessages();
		$isError = false;

		foreach ($messages->getItems() as $message) {
			if ($message->getType() == 'error') {
				$isError = true;
			}
		}

		if ($isError) {
			$email = $this->getRequest()->getPost('email');
			$firstname = $this->getRequest()->getPost('firstname');
			$lastname = $this->getRequest()->getPost('lastname');
			Mage::getSingleton('oneall/session')
				->setEmail($email)
				->setFirstname($firstname)
				->setLastname($lastname);
			
			$this->_redirect('oneall/rpx/duplicate');
		}

		return;
	}

	public function duplicateAction() {
		$session = $this->_getSession();

		// Redirect if user is already authenticated
		if ($session->isLoggedIn()) {
			$this->_redirect('customer/account');
			return;
		}

		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$block = Mage::getSingleton('core/layout')->getBlock('customer_form_register');
		$block->setUsername(Mage::getSingleton('oneall/session')->getEmail());
		$block->getFormData()->setEmail(Mage::getSingleton('oneall/session')->getEmail());
		$block->getFormData()->setFirstname(Mage::getSingleton('oneall/session')->getFirstname());
		$block->getFormData()->setLastname(Mage::getSingleton('oneall/session')->getLastname());
		$this->renderLayout();
	}

	public function loginPostAction() {
		parent::loginPostAction();
	}

	protected function _loginPostRedirect() {
		$session = $this->_getSession();
		
		if ($session->isLoggedIn()) {
			if ($profile = Mage::getSingleton('oneall/session')->getIdentifier()) {
				$customer = $session->getCustomer();
				Mage::helper('oneall/identifiers')
						->save_identifier($customer->getId(), $profile);
				Mage::getSingleton('oneall/session')->setIdentifier(false);
			}
		}

		// Mage::getModel('customer/customer')->load($customer->getId())->save();
		parent::_loginPostRedirect();
	}

	public function removeIdAction() {
		$session = $this->_getSession();
		$id = $this->getRequest()->getParam('delete');

		Mage::helper('oneall/identifiers')
				->delete_identifier($id);
		$session->addSuccess('Identity removed');
		$this->_redirect('customer/account');
	}

}