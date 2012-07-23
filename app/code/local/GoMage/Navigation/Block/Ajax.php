<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 1.0
 */

	class GoMage_Navigation_Block_Ajax extends Mage_Core_Block_Abstract{
		
		
		protected $eval_js = array();
		
		public function addEvalJs($str, $param = 'eval_js'){
			
			$this->setData($param, $this->getData($param).";".$str);
			
		}
		
	}