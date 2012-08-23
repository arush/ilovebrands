<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php 
 *
 * @category   FME
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
 
class FME_Manufacturers_Block_Adminhtml_Manufacturers_Renderer_Gridlogo extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	 protected static $img = "";
	 protected static $html = "";
	 protected static $imgPath = "";
	 
    /** 
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    
    protected function _getValue(Varien_Object $row)
    {
        $dored = false;
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
		$manufacturers_id  = $row->getData("manufacturers_id");
		
		try {	
			$obj = $collection = Mage::getModel('manufacturers/manufacturers')->load($manufacturers_id)->getData();
			$img = $obj["m_logo"];
			
			$x = 75;  
        	$y = 75; 
			$color = "255,255,255";
		
			if($img != "") {
				$imgPath = Mage::helper('manufacturers')->getResizedUrl($img,$x,$y,$color);	
				
			} else {		
				$imageFile =  "manufacturers/files/n/i/no_image_available.jpg";
				$imgPath = Mage::helper('manufacturers')->getResizedUrl($imageFile,$x,$y,$color);
				
			}

			$html = "<img src='".$imgPath."' border=0 />";
			
		} catch(Exception $e){}
		
		return $html; 
	}


}
