<?php

	class Sonassi_Wordpress_Block_Feed extends Mage_Core_Block_Template {
		
	 	public function getRssItems($url) {
			if($xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA)) {
			$result["title"] = $xml->xpath("/rss/channel/item/title");
			$result["link"] = $xml->xpath("/rss/channel/item/link");
			$result["description"] = $xml->xpath("/rss/channel/item/description");
			$result["content"] = $xml->xpath("/rss/channel/item/content:encoded/text()");
			$result["pubDate"] = $xml->xpath("/rss/channel/item/pubDate");
			
			foreach($result as $key => $attribute) {
				$i=0;
				foreach($attribute as $element) {
					$ret[$i][$key] = (string)$element;
					$i++;
				}
			} 
				return $ret; 
			} else {
				return false; 
			}
		} 	
		
	}
