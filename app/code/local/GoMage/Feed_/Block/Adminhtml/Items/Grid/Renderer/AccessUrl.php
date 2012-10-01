<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

class GoMage_Feed_Block_Adminhtml_Items_Grid_Renderer_AccessUrl extends  Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	
	public function render(Varien_Object $feed){
		
		if($url = $feed->getUrl()){
			return sprintf('<a href="%s" target="_blank">%s</a>', $url, $url);
		}
		
	}
}