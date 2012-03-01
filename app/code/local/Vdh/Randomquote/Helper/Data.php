<?php
class Vdh_Randomquote_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getQuotes() {
		$mappings = Mage::getStoreConfig('randomquote/general/quotes');

		$mappings = unserialize($mappings);
		$return = array();
		foreach($mappings as $k => $v) {
			$return[] = $v;
		}
		$value = array();		
		foreach ($return as $key => $row) {
		    $value[$key]  = $row['order'];
		}            	
		return $return;

	}
	
	public function getStyles() {
		$mappings = Mage::getStoreConfig('randomquote/general/styles');
		$mappings = unserialize($mappings);
		$return = array();
		foreach($mappings as $k => $v) {
			$return[] = $v;
		}
		$value = array();
		foreach ($return as $key => $row) {
		    $value[$key]  = $row['order'];
		}            	
		array_multisort($value, SORT_ASC, $return);
		return $return;

	}
	
	public function getProfileDate() {
		$cutoff = Mage::getStoreConfig('randomquote/general/cutoff');
		$deliveryDate = Mage::getStoreConfig('randomquote/general/delivery_date');
		$billingDate = Mage::getStoreConfig('randomquote/general/billing_date');
		$today = mktime(0,0,0, date('m'),date('d')+$cutoff, date('Y'));
		$nextDelivery = mktime(0,0,0,date('m')+1, $deliveryDate, date('Y'));
		
		$return = array(
			"billing_date" => date('d/m/Y 00:00', mktime(0,0,0,date('m')+1, $billingDate, date('Y'))),
			"delivery_date" => date('d/m/Y', mktime(0,0,0,date('m')+1, $deliveryDate, date('Y')))			
		);
		if ($today > $nextDelivery) {
			$return = array(
				"billing_date" => date('d/m/Y 00:00', mktime(0,0,0,date('m')+2, $billingDate, date('Y'))),
				"delivery_date" => date('d/m/Y', mktime(0,0,0,date('m')+2, $deliveryDate, date('Y')))			
			);

		}
		return $return;
	}

	public function getProfileEasyDate() {
		$orderDelay = Mage::getStoreConfig('randomquote/general/delivery_time');
		$deliverySpread = Mage::getStoreConfig('randomquote/general/delivery_margin');
		$cutoffHour = Mage::getStoreConfig('randomquote/general/cutoff_hour');
		$cutoffMin = Mage::getStoreConfig('randomquote/general/cutoff_min');
		
		$now = time();
		$passedCutoff = false;
		$dispatch = $this->getNextWorkDayTime(mktime($cutoffHour,$cutoffMin,0, date('m'),date('d'), date('Y'))); //today's cutoff time
		
		// have you passed today's cutoff?
		if(date('d/m/Y H:i',$now)>date('d/m/Y H:i',$dispatch)) {
			$passedCutoff = true;
			$dispatch = $this->getNextWorkDayTime(
				mktime(12,00,0, date('m'),date('d')+1, date('Y'))
			);
		}
		
		// get delivery spread
		$d1 = $dispatch + ($orderDelay * 24 * 60 * 60);
		$d1 = $this->getNextWorkDayTime($d1);
		
		$d2 = $d1 + ($deliverySpread * 24 * 60 * 60);
		$d2 = $this->getNextWorkDayTime($d2);
		
		// prepare for output
		$d1_words = getdate($d1);
		$d2_words = getdate($d2);
		
		$delivery_date = $d1_words["weekday"] . ' ' . $d1_words["mday"] . ' ' . substr($d1_words["month"],0,3);
		$delivery_date2 = $d2_words["weekday"] . ' ' . $d2_words["mday"] . ' ' . substr($d2_words["month"],0,3);

		$return = array(
			"passed_cutoff" => $passedCutoff,
			"now" => $now,
			"delivery_date" => $delivery_date,
			"delivery_date2" => $delivery_date2
		);
		return $return;
	}
	
	public function getInvitesRemaining() {
	
		$invites = Mage::getModel('rewardsref/referral')->getCollection()
		->addFieldToFilter('referral_parent_id', Mage::getSingleton('customer/session')->getId())
		->addFieldToFilter('referral_status', 1);
		
		$invitesRemaining = 50 -($invites->count() % 50);
		return $invitesRemaining;
	}
	
	public function getInvitesConverted() {
		
		$invites = Mage::getModel('rewardsref/referral')->getCollection()
			->addFieldToFilter('referral_parent_id', Mage::getSingleton('customer/session')->getId())
			->addFieldToFilter('referral_status', 1);
		
		return $invites->count();
	}

	public function getTotalInvitesSent() {
	
		$invites = Mage::getModel('rewardsref/referral')->getCollection();
		
		$invitesRemaining = $invites->count();
		return $invitesRemaining;
	
	}
	
	public function getInvitesSent() {
	
		$invites = Mage::getModel('rewardsref/referral')->getCollection()
		->addFieldToFilter('referral_parent_id', Mage::getSingleton('customer/session')->getId());
		
		$invitesRemaining = 50 -($invites->count() % 50);
		return $invitesRemaining;
	
	}
	
	public function getNextWorkDayTime($date=null) 
	{ 
	    $time = is_string($date) ? strtotime($date) : (is_int($date) ? $date : time()); 
	    $y = date('Y', $time); 
	    // calculate holidays 
	    $holidays = array(); 
	    // month/day (jan 1st). iteration/wday/month (3rd monday in january) 
	    $hdata = array('1/1'/*newyr*/, '12/25'/*xmas*/, '12/26'/*box*/); 
	    foreach ($hdata as $h1) { 
	        $h = explode('/', $h1); 
	        if (sizeof($h)==2) { // by date 
	            $htime = mktime(0, 0, 0, $h[0], $h[1], $y); // time of holiday 
	            $w = date('w', $htime); // get weekday of holiday 
	            $htime += $w==0 ? 86400 : ($w==6 ? -86400 : 0); // if weekend, adjust 
	        } else { // by weekday 
	            $htime = mktime(0, 0, 0, $h[2], 1, $y); // get 1st day of month 
	            $w = date('w', $htime); // weekday of first day of month 
	            $d = 1+($h[1]-$w+7)%7; // get to the 1st weekday 
	            for ($t=$htime, $i=1; $i<=$h[0]; $i++, $d+=7) { // iterate to nth weekday 
	                 $t = mktime(0, 0, 0, $h[2], $d, $y); // get next weekday 
	                 if (date('n', $t)>$h[2]) break; // check that it's still in the same month 
	                 $htime = $t; // valid 
	            } 
	        } 
	        $holidays[] = $htime; // save the holiday 
	    } 
	    for ($i=0; $i<5; $i++, $time+=86400) { // 5 days should be enough to get to workday 
	        if (in_array(date('w', $time), array(0, 6))) continue; // skip weekends 
	        foreach ($holidays as $h) { // iterate through holidays 
	            if ($time>=$h && $time<$h+86400) continue 2; // skip holidays 
	        } 
	        break; // found the workday 
	    } 
	    return $time; 
	} 
	
}