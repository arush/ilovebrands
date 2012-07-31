<?php

/**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */
	
class GoMage_Feed_Model_Observer{
	
	static function proccessFeeds(){
		$collection = Mage::getResourceModel('gomage_feed/item_collection');
		
		$current_time = date('G');
		
		$collection->getSelect()->where('`upload_day` like "%'.strtolower(date('D')).'%"');
				
		foreach($collection as $feed){
			
			if(date('d.m.Y:H') == date('d.m.Y:H', strtotime($feed->getData('cron_started_at')))){
				
				continue;
				
			}
			
			switch (intval($feed->getData('upload_interval')))
			{
			    case 12:
			    case 24:
			    		$upload_hour_from = intval($feed->getData('upload_hour'));
			    		
			    		if($upload_hour_from != $current_time){
			    			continue 2;
			    		}
			    		
                        if ((strtotime($feed->getData('cron_started_at')) + $feed->getData('upload_interval')*60*60) > time())
                        {
                            continue 2;
                        } 
			        break;
			    default:    
    			        $current_time = date('G');
    			        $upload_hour_from = intval($feed->getData('upload_hour'));
    			        $upload_hour_to = intval($feed->getData('upload_hour_to'));
    			        if (!$upload_hour_to) $upload_hour_to = 24;
    			        
    			        $hours = array();
    			        if ($upload_hour_from > $upload_hour_to)
    			        {
    			            for($i = $upload_hour_from; $i <= 23; $i++)
    			                $hours[] = $i;
    			            for($i = 0; $i <= $upload_hour_to; $i++)
    			                $hours[] = $i;    
    			        }           			         
    			        else
    			        {
    			            for($i = $upload_hour_from; $i <= $upload_hour_to; $i++)
    			            {
    			                if ($i == 24)
    			                    $hours[] = 0;
    			                else
    			                    $hours[] = $i;
    			            }        
    			        }
    			        
    			        if (!in_array($current_time, $hours)){
    			            continue 2;
    			        }    
    			            
    			        if ((strtotime($feed->getData('cron_started_at')) + $feed->getData('upload_interval')*60*60) > time())
                        {
                            continue 2;
                        }    
			}
			
			try{
				Mage::app()->setCurrentStore($feed->getStoreId());
				
				$cron_started_at = date('Y-m-j H:00:00', time());
				$feed->setData('cron_started_at', $cron_started_at);
				$feed->save();
								
				$feed->generate();
				$feed->ftpUpload();
				
				$feed->setData('restart_cron', 0);
				$feed->save();				
				
			}catch(Exception $e){
				
			    $feed->setData('restart_cron', intval($feed->getData('restart_cron')) + 1);
			    $feed->save();
			    
				continue;
				
			}

		}
		
	}
	
	static function generateAll(){
		
		
		$collection = Mage::getResourceModel('gomage_feed/item_collection');
		
		foreach($collection as $feed){
			try{
				Mage::app()->setCurrentStore($feed->getStoreId());
				$feed->generate();
			}catch(Exception $e){
				continue;
			}
		}
		
	}
	
	static function uploadAll(){
		
		$collection = Mage::getResourceModel('gomage_feed/item_collection');
		
		foreach($collection as $feed){
			try{
				Mage::app()->setCurrentStore($feed->getStoreId());
				$feed->ftpUpload();
			}catch(Exception $e){
				continue;
			}
		}			
	}
	
	static public function checkK($event)
    {			
		$key = Mage::getStoreConfig('gomage_activation/feed/key');			
		Mage::helper('gomage_feed')->a($key);			
	} 
	
}