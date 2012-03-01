<?php
class Vdh_Randomquote_GaugeController extends Mage_Core_Controller_Front_Action {

	public function pointsAction() {
	
		$invitesRemaining = Mage::helper('randomquote')->getInvitesRemaining();		
		$gaugeValue = 50-$invitesRemaining;

		
		if (!$this->getRequest()->getParam('init')) { $gaugeValue = 50; }
		$return = array(
			"links"		=> 10,
			"people"	=> $gaugeValue,
			"read"		=> 4,
			"toppages"	=> array(),
			"direct"	=> 14,
			"visits"	=> 50,
			"subscr"	=> 1,
			"pages"		=> 23,
			"search"	=> 3,
			"crowd"		=> 0,
			"domload"	=> 6000.0,
			"visit"		=> 50.0,
			"write"		=> 0,
			"idle"		=> 37,
			"internal"	=> 6,
			"social"	=> 7,
			"new"		=> 50
		);
		
		echo json_encode($return);

//		echo '{"links": 10, "people": 1, "read": 4, "toppages": [], "direct": 14, "visits": 41, "subscr": 1, "pages": 23, "search": 3, "crowd": 0, "domload": 6000.0, "visit": 60.0, "write": 0, "idle": 37, "internal": 6, "social": 7, "new": 10}';
	
	}
	
	public function weeklyAction() {
	
		$invitesSent = Mage::helper('randomquote')->getTotalInvitesSent();		
		$gaugeValue = $invitesSent +1; //plus one because of the graphic offset

		
		if (!$this->getRequest()->getParam('init')) { $gaugeValue = 1000; }
		$return = array(
			"links"		=> 1000,
			"people"	=> $gaugeValue,
			"read"		=> 4,
			"toppages"	=> array(),
			"direct"	=> 14,
			"visits"	=> 1000,
			"subscr"	=> 1,
			"pages"		=> 23,
			"search"	=> 3,
			"crowd"		=> 0,
			"domload"	=> 6000.0,
			"visit"		=> 50.0,
			"write"		=> 0,
			"idle"		=> 37,
			"internal"	=> 6,
			"social"	=> 7,
			"new"		=> 1000
		);
		
		echo json_encode($return);

//		echo '{"links": 10, "people": 1, "read": 4, "toppages": [], "direct": 14, "visits": 41, "subscr": 1, "pages": 23, "search": 3, "crowd": 0, "domload": 6000.0, "visit": 60.0, "write": 0, "idle": 37, "internal": 6, "social": 7, "new": 10}';
	
	}

}


