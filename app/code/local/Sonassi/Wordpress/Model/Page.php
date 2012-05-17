<?php

	class Sonassi_Wordpress_Model_Page {
		
		public $buffer;
		public $title;
		public $head;
		public $sidebar;
		public $content;
		public $cmspages;
		public $meta;
		public $commentForm;

		public function __construct()
		{
			$this->getPage();
		}
		
		public function get_meta_data($html) {
		
			$meta = array();
	    preg_match_all('|<meta[^>]+name=\\"([^"]*)\\"[^>]+content="([^\\"]*)"[^>]+>|i', $html, $out,PREG_PATTERN_ORDER);
	
	    for ($i=0;$i < count($out[1]);$i++) {
	      if (strtolower($out[1][$i]) == "keywords") $meta['setKeywords'] = $out[2][$i];
	      if (strtolower($out[1][$i]) == "description") $meta['setDescription'] = $out[2][$i];
	      if (strtolower($out[1][$i]) == "robots") $meta['setRobots'] = $out[2][$i];
	    }
		
			if (is_array($meta))	
				return $meta;
			else
				return false;
		}		
		
		public function getMeta()
		{
			if (!$this->meta) {
				
				$matches;
				if (preg_match('/<!--head(.*?)end head-->/is',$this->buffer,$matches)) {
					$this->buffer = str_replace($matches[1],"",$this->buffer);
					$this->meta = $this->get_meta_data($matches[1]);
					return $this->meta;
				} else {
					return false;
				}
				
			} else {
				return $this->meta;
			}			
			
		}
		
		public function generateBreadcrumbs()
		{
			$breadcrumbs = array();
			$breadcrumbs[] = array("label"=>"Home","link"=>"/");
			$path_run = "";
			$i = count($breadcrumbs);
			
			$uri = explode("/",$_SERVER['REQUEST_URI']);
			array_shift($uri);
			
			foreach ($uri as $path) {
				if (!empty($path)) {
					$path_run .= "/".$path;
					$breadcrumbs[$i] = array("label"=>ucwords($path),"link"=>$path_run);
					$i++;
				}
			}
			
			$breadcrumbs[$i-1]["link"] = "";
			
			return $breadcrumbs;
			
		}

		public function getPage()
		{

			$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$_SERVER['REQUEST_URI'];
			if (@$file = file_get_contents($url)) {				
				
					$type = end($http_response_header);					
					if ((strpos($type,"text/html") === false && strpos($type,"application/x-httpd-php") === false)
								 || strpos($type,"text/xml") !== false ) {
						if (file_exists($url)) {
							foreach ($http_response_header as $header) {
								header($header);
							}
							readfile($url);
							exit();
						} else if ( strpos($type,"text/xml") !== false ) {
							readfile($url);
							exit();
						} else {
							header("Location: /no-route");
							exit();
						}
					}							
				
  				$this->buffer = $file; 		
  				return $this->buffer;
  		} else {
  			header("Location: /blog/");
  			exit();
  		}
		}
		
		public function getBuffer()
		{
			if (empty($this->buffer))
				return $this->getPage();
			else
				return $this->buffer;
		}
		
		public function getContent()
		{
			
			$this->buffer = $this->getCommentForm();
		
			if (!$this->content) {
				// Sanitise the column
				$this->getSidebar();
				$this->content = &$this->buffer;
				return $this->content;	
			} else {
				return $this->content;
			}
		}
		
		public function getTitle()
		{

			if (!$this->title) {
			
				$matches;
				if (preg_match('/<span id="pagetitle">(.*?)<\/span>/i',$this->buffer,$matches)) {
					$this->buffer = str_replace($matches[1],"",$this->buffer);
					$this->title = &$matches[1];
					return $matches[1];
				} else {
					return false;
				}
				
			} else {
				return $this->title;
			}
		}
		
		public function getHead()
		{
			if (!$this->head) {
			
				$matches;
				if (preg_match('/<!--head(.*?)end head-->/is',$this->buffer,$matches)) {
					$this->buffer = str_replace($matches[1],"",$this->buffer);
					$this->head = &$matches[1];
					return $matches[1];
				} else {
					return false;
				}
				
			} else {
				return $this->head;
			}
		}
		
		public function getSidebar()
		{
			if (!$this->sidebar) {
				$this->getCmsPages();
				
				$matches;
				if (preg_match('/<!--sidebar-->(.*?)<!--end sidebar-->/si',$this->buffer,$matches)) {
					$this->buffer = str_replace($matches[1],"",$this->buffer);
					$this->sidebar = &$matches[1];
					return $matches[1];
				} else {
					return false;
				}
				
			} else {
				return $this->sidebar;
			}
		}		
		
		public function getCommentForm() {
			
			if (!$this->commentForm) {
				
				$this->getCmsPages();
				
				$commentFormPage = Mage::app()->getLayout()->createBlock('core/template')->setTemplate('wordpress/comments-form.phtml')->toHtml();
				$this->commentForm = str_replace("<!--magento-comment-form-->",$commentFormPage,$this->buffer);
			}
			return $this->commentForm;	
		}
				
		public function getCmsPages()
		{
			if (!$this->cmspages) {
				
				$matches;
				$store = Mage::app()->getStore()->getCode();
    
		    $cms_pages = Mage::getModel('cms/page')->getCollection();
		    $cms_pages->addStoreFilter($store);
		    $cms_pages->load();

        $last = count($cms_pages);
        $ctr = 0;
        $pages = "";
        
        foreach($cms_pages as $_page)
        {
            $data = $_page->getData();
            if($data['identifier']=='no-route' || 
            		$data['identifier']=='error' || 
            		$data['identifier']=='empty' || 
            		$data['identifier']=='fof' || 
            		$data['identifier']=='phil' || 
            		$data['identifier']=='enable-cookies' 
            	 )
                continue;

            $pages .= '<li><a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$data['identifier'].'">'.ucwords($data['title']).'</a></li>';
        }
				
				$this->buffer = str_replace('<!--cmspages-->',$pages,$this->buffer);
				$this->cmspages = &$pages;
				return $pages;
				
			} else {
				return $this->cmspages;
			}			
			
		}
			
	
	}
