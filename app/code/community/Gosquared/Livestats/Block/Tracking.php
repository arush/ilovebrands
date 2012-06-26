<?php
class Gosquared_Livestats_Block_Tracking extends Mage_Core_Block_Template {
	public function getTrackingCode() {
		//Get params
		$acct = Mage::getStoreConfig('livestats/settings/acctcode');
		$name = Mage::getStoreConfig('livestats/settings/userdisplay');
		$admintrack = Mage::getStoreConfig('livestats/settings/admintrack');
		
		//First of all check if the acct code is valid.
		if($acct !='' && $acct != 'GSN-000000-X' && preg_match('/GSN-[0-9]{6,7}-[A-Z]{1}/', $acct) && $admintrack) {
			//acct code is valid so continue

			//If the user is not a guest then find their username
			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				//name->0 is Off; name->1 is User ID; name->2 is Name; name->3 is Email
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				switch ($name) {
					case 1:
						$GSUsername = $customer->getCustomerID();
						break;
						
					case 2:
						$GSUsername = $customer->getName();
						break;
						
					case 3:
						$GSUsername = $customer->getEmail();
						break;
					
					default:
						$GSUsername = '';
						break;
				}
			} else if(Mage::getSingleton('admin/session')->isLoggedIn()) {
				$user = Mage::getSingleton('admin/session')->getUser();
				switch ($name) {
					case 1:
						$GSUsername = $user->getUserID();
						break;
						
					case 2:
						$GSUsername = $user->getName();
						break;
						
					case 3:
						$GSUsername = $user->getEmail();
						break;
					
					default:
						$GSUsername = '';
						break;
				}
			}
			//Set the initial JS
			$GSJavascript = '

		<!--LiveStats Magento Plugin-->
		<script type="text/javascript">
					var GoSquared={};
					GoSquared.acct = "' . $acct . '";
					GoSquared.TrackDelay = 0;
					';
					if(isset($GSUsername)) {
						$GSJavascript .= 'GoSquared.VisitorName = "' . $GSUsername . '";
						';
					}
					$GSJavascript .= '(function(w){
				    function gs(){
				    	w._gstc_lt=+(new Date); var d=document;
				        var g = d.createElement("script"); g.type = "text/javascript"; g.async = true; g.src = "//d1l6p2sc9645hc.cloudfront.net/tracker.js";
				        var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(g, s);
				    }
				    w.addEventListener?w.addEventListener("load",gs,false):w.attachEvent("onload",gs);
				})(window);
		</script>
		<!--End LiveStats Magento Plugin-->

		';
			return $GSJavascript;
		}
	}
	
	/*protected function _toHtml() {
		$returnedJS = $this->_getTrackingCode();
		if ($returnedJS) {
			return $returnedJS;
		} else {
			return '';
		}
	}*/
}