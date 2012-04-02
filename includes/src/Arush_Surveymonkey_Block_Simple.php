<?php
class Arush_Surveymonkey_Block_Simple
    extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{

    /**
     * Produces a simple survey html
     *
     * @return string
     */
    protected function _toHtml() {

    	$this->getLayout()->getBlock('root')->setTemplate($this->getData('template_path'));
		$block = $this->getLayout()->getBlock('root');
		
		$block = $block->setData(
			array(
				'embed'		=> $this->getData('embed')
			)
		);
		$html = $block->toHtml();
		return $html;
    }

}