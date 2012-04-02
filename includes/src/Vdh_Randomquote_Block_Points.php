<?php
class Vdh_Randomquote_Block_Points extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _toHtml() {

    	$this->getLayout()->getBlock('root')->setTemplate($this->getData('template_path'));
		$block = $this->getLayout()->getBlock('root');
		
		if (Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn ()) {
			$customer = Mage::getSingleton ( 'rewards/session' )->getSessionCustomer ();
			$pointsSummary = $customer->getPointsSummary();
			$points = (int)substr_replace($pointsSummary,"",-7);			
		} else {
			$points = 0;
		}


		$block = $block->setData(
			array(
				'points'	=> $points,
				'text'		=> $this->getData('text')
			)
		);
		$html = $block->toHtml();
		return $html;
    }

}