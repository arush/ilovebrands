<?php class Vdh_Randomquote_QuoteController extends Mage_Core_Controller_Front_Action {

	public function quoteAction() {
		$position = $this->getRequest()->getParam('position');		
		$layout = ($this->getRequest()->getParam('layout')) ? $this->getRequest()->getParam('layout') : 'invite';
		$quote = Mage::getModel('randomquote/quote')->load(isset($position) ? $position : false);
		
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('vdh/randomquote/'.$layout.'.phtml');
		$this->getLayout()->getBlock('root')->setQuote($quote);
    	$this->renderLayout();	

		
	}

	public function styleAction() {
		$position = $this->getRequest()->getParam('position');		
		$layout = ($this->getRequest()->getParam('layout')) ? $this->getRequest()->getParam('layout') : 'style';
		$style = Mage::getModel('randomquote/style')->load(isset($position) ? $position : false);
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('vdh/randomquote/'.$layout.'.phtml');
		$this->getLayout()->getBlock('root')->setStyle($style);
    	$this->renderLayout();	

		
	}


}