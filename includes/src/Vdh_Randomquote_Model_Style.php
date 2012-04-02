<?php
class Vdh_Randomquote_Model_Style extends Varien_Object {

	public function load($position = false) {
	
		$helper = Mage::helper('randomquote');
	
		$styles = $helper->getStyles();
		if ($position === false || $position >= sizeof($styles)) {
			$random = rand(0, sizeof($styles)-1);
			$style = $styles[$random];			
		} else {
			$style = $styles[$position];
		}
		$this->setData($style);
		return $this;
	}
}		