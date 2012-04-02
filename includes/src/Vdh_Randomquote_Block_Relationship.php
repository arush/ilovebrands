<?php
class Vdh_Randomquote_Block_Relationship extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _toHtml() {

    	$this->getLayout()->getBlock('root')->setTemplate($this->getData('template_path'));
		$block = $this->getLayout()->getBlock('root');
		
		$collection = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id', array('in' => array(3035,3036,3037)));
			
		$block = $block->setData(array('collection' => $collection));
		$html = $block->toHtml();
		return $html;
    }

}