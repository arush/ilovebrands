<?php

/**
 * Tokens grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Block_Adminhtml_Tokencard extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    protected function _prepareLayout()
    {
		if(!$this->getRequest()->isXmlHttpRequest()){
    		$this->getLayout()->getBlock('head')
    		->addItem('skin_css', 'sagepaysuite/css/sagePaySuite.css');
		}
        return parent::_prepareLayout();
    }

    public function __construct()
    {
    	$this->_blockGroup = 'sagepaysuite';
        $this->_controller = 'adminhtml_tokencard';
        $this->_headerText = Mage::helper('sagepaysuite')->__('(%s active in Sage Pay) Sage Pay Cards', $this->_getServiceCount());

        parent::__construct();

        $this->_removeButton('add');

    }

    protected function _getServiceCount()
    {
    	$count = '?';

    	try{

    		$r = Mage::getModel('sagepayreporting/sagepayreporting')->getTokenCount();
    		$count = (int)$r->totalnumber;

    	}catch(Exception $e){
    		Sage_Log::logException($e);
    	}

    	return $count;
    }

}