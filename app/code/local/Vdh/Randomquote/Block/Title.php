<?php
class Vdh_Randomquote_Block_Title extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _beforeToHtml() {
        parent::_beforeToHtml();
        $blockId = $this->getData('block_id');
        if ($blockId) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if ($block->getIsActive()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $this->setText($processor->filter($block->getContent()));
            }
        }
        return $this;
    }

}