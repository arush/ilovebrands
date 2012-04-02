<?php

class Arush_Oneall_Block_Share extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

	public function rpx_social_icons() {
        $social_pub = Mage::getStoreConfig('oneall/vars/socialpub');
        $social_providers = array_filter(explode(',', $social_pub));
        if (is_array($social_providers)) {
            $rpx_social_icons = '';
            foreach ($social_providers as $val) {
                $rpx_social_icons .= '<div class="jn-icon jn-size16 jn-' . $val . '"></div>';
            }
            $buttons = '<div class="rpx_social_icons">' . $rpx_social_icons . '</div>';
            return $buttons;
        }
        return false;
	}

	/**
	 * Adds a link to open the Oneall authentication dialog
	 *
	 * @return string
	 */
	protected function _toHtml() {
        $link = '';
        
        if ($icons = $this->rpx_social_icons()) {
            $link .= '<div class="rpxsocial rpx_tooltip" onclick="RPXNOW.loadAndRun([\'Social\'], function () { var activity = new RPXNOW.Social.Activity(\'Share:\', \'' . Mage::getSingleton('cms/page')->getTitle() . '\', \'' . Mage::helper('core/url')->getCurrentUrl() . '\'); activity.setUserGeneratedContent(\'' . $this->getShareText() . '\'); RPXNOW.Social.publishActivity(activity); });">';
            $link .= '<span class="rpxsharebutton">share</span><div class="rpx_share_tip">Share this on:<br />' . $icons . '</div></div>';
        }

		return $link;
	}

	protected function _prepareLayout() {

		/*
		 * Doesn't work on inline widgets because layout isn't loaded until
		 * after the head has been written to the page. Fix.
		 *
		if($this->getLayout()->getBlock('arush_oneall_styles')==false) {
			$block = $this->getLayout()
				->createBlock('core/template', 'arush_oneall_styles')
				->setTemplate('arush/oneall/styles.phtml');
			$this->getLayout()->getBlock('head')->insert($block);
		}
		*/

		if($this->getLayout()->getBlock('arush_oneall_scripts')==false) {
			$block = $this->getLayout()
				->createBlock('core/template', 'arush_oneall_scripts')
				->setTemplate('arush/oneall/scripts.phtml');
			$this->getLayout()->getBlock('before_body_end')->insert($block);
		}

		parent::_prepareLayout();
	}

}