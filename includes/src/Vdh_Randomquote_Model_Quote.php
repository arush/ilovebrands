<?php
class Vdh_Randomquote_Model_Quote extends Varien_Object {

	public function load($position = false) {
	
		$helper = Mage::helper('randomquote');
	
		$quotes = $helper->getQuotes();
		if ($position === false || $position >= sizeof($quotes)) {
			$random = rand(0, sizeof($quotes)-1);
			$quote = $quotes[$random];			
		} else {
			$quote = $quotes[$position];
		}
		$this->setData($quote);
		return $this;
	}
}		