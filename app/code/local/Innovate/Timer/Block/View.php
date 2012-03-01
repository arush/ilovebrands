<?php
/**
* Innovate Timer block
*
* @codepool   Local
* @category   Innovate
* @package    Innovate_Timer
* @module     Timer
*/
class Innovate_Timer_Block_View extends Mage_Core_Block_Template
{
    public function getCountdownTo()
      {        
          return (string)Mage::getStoreConfig('timer/view/timer_countdown_to');
      }
	public function getBannerClosed()
      {        
          return (boolean)Mage::getStoreConfig('timer/view/banner_closed');
      }
	public function getCategoryAnchor1()
	  {
		  return (int)Mage::getStoreConfig('timer/view/category_anchor1');
	  }
	public function getCategoryAnchor2()
	  {
		  return (int)Mage::getStoreConfig('timer/view/category_anchor2');
	  }
	  
}
