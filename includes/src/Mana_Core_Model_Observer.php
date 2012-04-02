<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_Core_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Add layout handles from config.xml files (handles event "controller_action_layout_load_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function addLayoutHandles($observer) {
		/* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
		/* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();

		if (Mage::getConfig()->getNode('m_layout')) {
			foreach (Mage::getConfig()->getNode('m_layout')->children() as $name => $config) {
				if (in_array($name, $layout->getUpdate()->getHandles())) {
					foreach ($config->children() as $action => $actionConfig) {
						if (isset($actionConfig['if'])) {
							$method = (string) $actionConfig['if'];
							$args = array();
							foreach ($actionConfig->children() as $arg) {
								$args[] = (string) $arg;
							}
							$visible = call_user_func_array(array($this, $method), $args);
						}
						else {
							$visible = true;
						}
						if ($visible) {
							$method = (string) $actionConfig['action'];
							$this->$method($layout, $name, $actionConfig);
						}
					}
				}
			}
		}
	}
	
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * After blocks are generated change their properties (handles event "controller_action_layout_generate_blocks_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function postProcessBlocks($observer) {
		/* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
		/* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();
		
		if (Mage::getConfig()->getNode('m_blocks')) {
			foreach (Mage::getConfig()->getNode('m_blocks')->children() as $name => $config) {
				if (in_array($name, $layout->getUpdate()->getHandles())) {
					foreach ($config->children() as $action => $actionConfig) {
						if (isset($actionConfig['if'])) {
							$method = (string) $actionConfig['if'];
							$args = array();
							foreach ($actionConfig->children() as $arg) {
								$args[] = (string) $arg;
							}
							$visible = call_user_func_array(array($this, $method), $args);
						}
						else {
							$visible = true;
						}
						if ($visible) {
							foreach ($this->_findBlocks($layout, $actionConfig) as $block) {
								$method = (string) $actionConfig['action'];
								$this->$method($block, $actionConfig);
							}
						}
					}
				}
			}
		}
	}
	
	
	protected function _findBlocks($layout, $actionConfig) {
		$result = array();
		
		if (isset($actionConfig['type'])) {
			$value = $block = Mage::getConfig()->getBlockClassName((string) $actionConfig['type']);
			foreach ($layout->getAllBlocks() as $block) {
				if ($block instanceof $value) {
					$result[] = $block;
				}
			}				
		}
		else {
			throw new Exception('Not implemented');
		}

		return $result;
	}
	
	// CONDITION METHODS
	
	public function flagSet($param) {
		return Mage::getStoreConfigFlag($param);
	} 
	public function flagNotSet($param) {
		return ! Mage::getStoreConfigFlag($param);
	} 
	public function valueEquals($param, $value) {
		return (Mage::getStoreConfig($param) == $value);
	} 
	public function valueNotEquals($param, $value) {
		return (Mage::getStoreConfig($param) != $value);
	} 
	
	// LAYOUT HANDLE METHODS
	
	public function addAfter($layout, $name, $actionConfig) {
		if ($handle = (string)$actionConfig['handle']) {
			$handles = $layout->getUpdate()->getHandles();
			$index = array_search($name, $handles);
			$layout->getUpdate()->resetHandles()->addHandle(array_merge(
				array_slice($handles, 0, $index + 1),
				array($handle),
				$index + 1 < count($handles) ? array_slice($handles, $index + 1) : array()
			));
		}
	}
	
	// BLOCK ACTION METHODS
	
	public function setTemplate($block, $actionConfig) {
		$block->setTemplate((string)$actionConfig['template']);
	}
	/**
	 * Adds css files to header (handles event "core_block_abstract_to_html_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function adhocCss($observer) {
	    /* @var $block Mage_Core_Block_Abstract */ $block = $observer->getEvent()->getBlock();
	    /* @var $transport Varien_Object */ $transport = $observer->getEvent()->getTransport();

	    if ($block->getNameInLayout() == 'head' && ($css = $block->getMCss())) {
	        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
	        $html = '';
	        foreach ($css as $relativeUrl) {
	            if ($files->getFilename($relativeUrl, 'css')) {
	                $html .= '<link rel="stylesheet" type="text/css" href="'.$files->getUrl($relativeUrl, 'css').'" />'."\n";
	            }
	        }
	        if ($html) {
	            $transport->setHtml($transport->getHtml().$html);
	        }
	    }
	}
}