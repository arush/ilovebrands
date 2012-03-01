<?php

/**
 * Retail Evolved - Facebook Like Button
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled with this
 * package in the file EVOLVED_EULA.txt.
 * It is also available through the world-wide-web at this URL:
 * http://retailevolved.com/eula-1-0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to service@retailevolved.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * You may edit this file, but only at your own risk, as long as it is within
 * the constraints of the license agreement. Before upgrading the module (not Magento), 
 * be sure to back up your original installation as the upgrade may override your
 * changes.
 *
 * @category   Evolved
 * @package    Evolved_Like
 * @copyright  Copyright (c) 2010 Kaelex Inc. DBA Retail Evolved (http://retailevolved.com)
 * @license    http://retailevolved.com/eula-1-0 (Retail Evolved EULA 1.0)
 */

class Evolved_Like_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CONFIG_PATH = 'evlike/evlike/';
	const GRID_CONFIG_PATH = 'evlike/evlike_grid/';
	const LIST_CONFIG_PATH = 'evlike/evlike_list/';
	const CATEGORY_CONFIG_PATH = 'evlike/evlike_category/';
	const CMS_CONFIG_PATH = 'evlike/evlike_cms/';
	
	public function getLikeConfig($configName) 
	{			
		$configValue = Mage::getStoreConfig(self::CONFIG_PATH . $configName);
		
		return $configValue;
	}
	
	public function getLikeGridConfig($configName)
	{
		$configValue = Mage::getStoreConfig(self::GRID_CONFIG_PATH . $configName);
		
		return $configValue;
	}
	
	public function getLikeListConfig($configName)
	{
		$configValue = Mage::getStoreConfig(self::LIST_CONFIG_PATH . $configName);
		
		return $configValue;
	}
	
	public function getLikeCategoryConfig($configName)
	{
		$configValue = Mage::getStoreConfig(self::CATEGORY_CONFIG_PATH . $configName);
		
		return $configValue;
	}
	
	public function getLikeCmsConfig($configName)
	{
		$configValue = Mage::getStoreConfig(self::CMS_CONFIG_PATH . $configName);
		
		return $configValue;
	}
	
	public function getLikeHtml($block, $object, $setCategory = false, $setCms = false) {
		$_layout = $block->getLayout();
		
		if ($setCategory) {
			$_childBlockName = 'category.likebutton';
		} else if ($setCms) {
			$_childBlockName = 'cms.likebutton';
		} else {
			$_childBlockName = 'likebutton';
		}
		
		// Add child block for button if necessary
		if(!$block->getChild($_childBlockName)) {
			$_likeBlock = $_layout->createBlock('evlike/like', $_childBlockName)
				->setTemplate('evlike/likebutton.phtml');
			
			$block->append($_likeBlock, $_childBlockName);
		} 
		
		// Add FBINIT block to page if necessary
		if(!$_layout->getBlock('ev_fb_init')) {
			$_initBlock = $_layout->createBlock('core/template', 'ev_fb_init')
				->setTemplate('evlike/fbinit.phtml');
				
			$_layout->getBlock('before_body_end')->append($_initBlock, 'ev_fb_init');
		}
		
		if(!$setCategory && !$setCms) {
			$block->getChild($_childBlockName)->setProduct($object);
		} else if ($setCms) {
			$block->getChild($_childBlockName)->setCms(true);
		} else {
			$block->getChild($_childBlockName)->setCategory($object);
		}
		
		return $block->getChildHtml($_childBlockName, false);
	}
	
	public function getFacebookLocale() 
	{
		$_locale = Mage::app()->getLocale()->getLocaleCode(); 
		if($_locale != "en_AU" && $_locale != "en_HK" && $_locale != "en_NZ") {
			$_locale = Mage::app()->getLocale()->getLocaleCode();
		} else {
			$_locale = "en_US";
		}
		
		return $_locale;
	}
}