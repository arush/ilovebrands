<?php
class Vdh_Randomquote_Block_Quickie extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _toHtml() {

    	$this->getLayout()->getBlock('root')->setTemplate($this->getData('template_path'));
		$block = $this->getLayout()->getBlock('root');
		
		$collection = Mage::getModel('catalog/product')->getCollection();
		$block = $block->setData(array('collection' => $collection));
		$html = $block->toHtml();
		return $html;
    }

}