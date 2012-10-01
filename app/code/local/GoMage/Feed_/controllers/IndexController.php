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

	class GoMage_Feed_IndexController extends Mage_Core_Controller_Front_Action{
	
		public function indexAction(){
			
			$response = array('result'=>0);
			
			if($feed_id = $this->getRequest()->getParam('id')){
 				
	 			$feed = Mage::getModel('gomage_feed/item')->load($feed_id);
	 			$start = intval($this->getRequest()->getParam('start'));
	 			$length = intval($this->getRequest()->getParam('length'));
	 			
	 			
	 			if($start >= 0 && $length >= 0){
	 				
	 				if($feed->getType() == 'csv'){
	 					
	 					$feed->writeTempFile($start, $length);
	 					
	 				}else{
	 					
	 					Mage::getModel('gomage_feed/item_block_product', array('feed'=>$feed, 'content'=>''))->writeTempFile($start, $length);
	 					
	 				}
	 				
	 				$response['result'] = 1;
	 				
	 			}
	 		}
	 		
	 		$this->getResponse()->setBody(Zend_Json::encode($response));
			
		}
		
	}