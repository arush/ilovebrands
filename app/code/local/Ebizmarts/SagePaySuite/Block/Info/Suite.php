<?php

/**
 * Abstract info block
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Info_Suite extends Mage_Payment_Block_Info_Cc
{

	public function getSpecificInformation()
	{
		$tokenId = $this->isTokenInfo();
		if(!$tokenId){
			return parent::getSpecificInformation();
		}

		return $this->helper('sagepaysuite/token')->getDataAsArray($tokenId);
	}

    protected function _construct()
    {
        parent::_construct();

        #Only when order is saved
        if((Mage::registry('current_order') || $this->getOnMemo() || $this->getOnInvoice()) && Mage::app()->getStore()->isAdmin()){
			$this->setChild('repeats.list', Mage::getModel('core/layout')->createBlock('core/template', 'repeats.li')->setTemplate('sagepaysuite/payment/info/repeats.phtml'));
            $this->setChild('refunds.list', Mage::getModel('core/layout')->createBlock('core/template', 'refunds.li')->setTemplate('sagepaysuite/payment/info/refunds.phtml'));
            $this->setChild('authorise.list', Mage::getModel('core/layout')->createBlock('core/template', 'authorise.li')->setTemplate('sagepaysuite/payment/info/authorises.phtml'));
        }

		if(Mage::getSingleton('core/translate')->getTranslateInline() === false && Mage::app()->getStore()->isAdmin()){ //For Emails
			$this->setTemplate('sagepaysuite/payment/info/base-basic.phtml');
		}else{
			$this->setTemplate('sagepaysuite/payment/info/base.phtml');
		}
    }

	public function getOnMemo()
	{
		$r = $this->getRequest();
		return (bool)(!is_null(Mage::registry('current_creditmemo')) || ($r->getControllerName() == 'sales_order_creditmemo' && $r->getActionName() == 'view'));
	}

	public function getOnInvoice()
	{
		$r = $this->getRequest();
		return (bool)(!is_null(Mage::registry('current_invoice')) || ($r->getControllerName() == 'sales_order_invoice' && $r->getActionName() == 'view'));
	}

	public function getTokenCard()
	{
		$token = $this->isTokenInfo();
		if($token){
			if(is_object($this->getInfo()->getOrder()) && is_object($this->getInfo()->getOrder()->getSagepayInfo()) && $this->getInfo()->getOrder()->getSagepayInfo()->getToken()){
				$t = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->loadByToken($this->getInfo()->getOrder()->getSagepayInfo()->getToken());
			}else{
				$t = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($this->getInfo()->getSagepayTokenCcId());
			}

            return $t;
		}

		return new Varien_Object;
	}

	public function isTokenInfo()
	{
                if(is_object($this->getInfo()->getOrder()) && is_object($this->getInfo()->getOrder()->getSagepayInfo())){
                    return (bool)(strlen($this->getInfo()->getOrder()->getSagepayInfo()->getToken()) > 0);
                }else if($this->getInfo()->getSagepayTokenCcId()){
                	return true;
                }
                return false;
	}

    public function getCcTypeName($type = null, $textOnly = false)
    {
        $types = Mage::getSingleton('sagepaysuite/config')->getCcTypesSagePayDirect(true);
        $ccType = ($type === null) ? $this->getInfo()->getCcType() : $type;
        if (isset($types[$ccType]) || $ccType == Ebizmarts_SagePaySuite_Model_Api_Payment::CARD_TYPE_PAYPAL) {

			if(true === $textOnly){
				return $types[$ccType];
			}

            $url = $this->helper('sagepaysuite')->getCcImage($types[$ccType]);
            $name = '<img alt="'.$types[$ccType].'" title="'.$types[$ccType].'" src="'.$this->helper('sagepaysuite')->getCcImage($types[$ccType]).'"/>';

            return $name;

        }
        return (empty($ccType)) ? Mage::helper('payment')->__('N/A') : $ccType;
    }

	public function getReleasesCollection()
	{
    	$refunds_collection = Mage::getResourceModel('sagepaysuite2/sagepaysuite_action_collection');
    	$refunds_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
                    ->setReleaseFilter()
    				->addOrder('action_date')
    				->load();

    	return $refunds_collection;
	}

	public function getAuthorisesCollection()
	{
    	$refunds_collection = Mage::getResourceModel('sagepaysuite2/sagepaysuite_action_collection');
    	$refunds_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
                    ->setAuthoriseFilter()
    				->addOrder('action_date')
    				->load();

    	return $refunds_collection;
	}

	public function getRefundsCollection()
	{
    	$refunds_collection = Mage::getResourceModel('sagepaysuite2/sagepaysuite_action_collection');
    	$refunds_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
                    ->setRefundFilter()
    				->addOrder('action_date')
    				->load();

    	return $refunds_collection;
	}

	public function getRepeatsCollection()
	{
    	$repeats_collection = Mage::getResourceModel('sagepaysuite2/sagepaysuite_action_collection');
    	$repeats_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
                    ->setRepeatFilter()
    				->addOrder('action_date')
    				->load();

    	return $repeats_collection;
	}

	public function getBasicRealTitle()
	{
		$title = $this->getMethod()->getTitle();

		if(is_object($this->getInfo()->getOrder())){
			$sagePayInfo = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->loadByParent($this->getInfo()->getOrder()->getId());
			if($sagePayInfo->getisPayPalTransaction()){
				$title = $this->getMethod()->getPayPalTitle();
			}
        }

        return $title;
	}

	protected function _getDetailUrl($vpsTxId)
	{
		return $this->helper('adminhtml')->getUrl('sagepayreporting/adminhtml_sagepayreporting/transactionDetailModal/', array('vpstxid'=>$vpsTxId));
	}

	public function getStoppedLabel($trn)
	{
		$txt = '-';

		if($trn->getVoided()){
			$txt = $this->__('Transaction is VOIDED.');
		}else if($trn->getAborted()){
			$txt = $this->__('Transaction is ABORTED.');
		}else if($trn->getCanceled()){
			$txt = $this->__('Transaction is CANCELLED.');
		}

		return $txt;
	}

	public function detailLink($vpsTxId)
	{
		$title = $this->__('Click here to view Transaction detail.');
		return sprintf('<a title="%s" id="%s" class="trn-detail-modal" href="%s">%s</a>', $title, str_replace(array('{','}'), '', $vpsTxId), $this->_getDetailUrl($vpsTxId), $vpsTxId);
	}

	public function getParentOrderLink($sagepay)
	{
		$lnk = '';

		if($sagepay->getParentTrnId()){
			$parent = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')->load($sagepay->getParentTrnId());
			if($parent->getOrderId()){
				$order = Mage::getModel('sales/order')->load($parent->getOrderId());

				$lnk .= '<a href="' . $this->getUrl('*/sales_order/view', array('order_id' => $order->getId())) . '">#' . $order->getIncrementId(). '</a>';

			}
		}

		return $lnk;
	}

	public function cs($str)
	{
		return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
	}

    public function toPdf()
    {
        $this->setTemplate('sagepaysuite/payment/info/pdf/base-basic.phtml');
        return $this->toHtml();
    }

	public function getThirdmanBreakdown($thirdmanId)
	{
		try{
			$breakdown = Mage::getModel('sagepayreporting/sagepayreporting')
				  ->getT3MDetail($thirdmanId);
		}catch(Exception $e){
			$breakdown = null;
			Mage::logException($e);
		}
		return $breakdown;
	}

}