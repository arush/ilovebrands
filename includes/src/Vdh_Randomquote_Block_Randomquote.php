<?php
class Vdh_Randomquote_Block_Randomquote extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _toHtml() {

    	$this->getLayout()->getBlock('root')->setTemplate($this->getData('template_path'));
		$block = $this->getLayout()->getBlock('root');
		
		$quote = Mage::getModel('randomquote/quote')->load();
		$block = $block->setData(array('quote' => $quote));
		$html = $block->toHtml();
		return $html;
    }

}